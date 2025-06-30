<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Exports\AdminAttendanceExport;
use App\Exports\AttendanceImportTemplate;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendance records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        // Base query with company filter
        $query = Attendance::with(['employee.user', 'employee.department', 'employee.designation'])
            ->whereHas('employee', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->latest('date');

        // Apply filters
        if ($request->filled('date_range')) {
            try {
                $dates = array_map('trim', explode(' - ', $request->date_range));
                
                // If only one date is provided, use it for both start and end
                if (count($dates) === 1 && !empty($dates[0])) {
                    $startDate = Carbon::parse($dates[0])->startOfDay();
                    $endDate = $startDate->copy()->endOfDay();
                } 
                // If two dates are provided
                elseif (count($dates) === 2) {
                    $startDate = Carbon::parse($dates[0])->startOfDay();
                    $endDate = Carbon::parse($dates[1])->endOfDay();
                    
                    // Validate date range is not too large (max 1 year)
                    if ($endDate->diffInDays($startDate) > 365) {
                        return redirect()->route('admin.attendance.index')
                            ->with('error', 'Date range cannot be more than 1 year.');
                    }
                } else {
                    throw new \Exception('Invalid date range format');
                }
                
                $query->whereBetween('date', [$startDate, $endDate]);
                
            } catch (\Exception $e) {
                return redirect()->route('admin.attendance.index')
                    ->with('error', 'Invalid date format. Please use YYYY-MM-DD format or select a date from the calendar.');
            }
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function($q) use ($request, $companyId) {
                $q->where('department_id', $request->department_id)
                  ->where('company_id', $companyId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->paginate(31)->withQueryString();

        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('title')->get();
        $employees = Employee::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.attendance.index', compact(
            'attendances',
            'departments',
            'designations',
            'employees'
        ));
    }

    /**
     * Store a newly created attendance record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $validated = $request->validate([
            'employee_id' => [
                'required',
                'exists:employees,id',
                function ($attribute, $value, $fail) use ($companyId) {
                    $employee = Employee::find($value);
                    if ($employee && $employee->company_id !== $companyId) {
                        $fail('The selected employee is invalid.');
                    }
                },
            ],
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after_or_equal:check_in',
            'status' => 'required|in:Present,Absent,Late,On Leave,Half Day',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Check if attendance already exists for this employee and date
        $existing = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $validated['date'])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Attendance record already exists for this employee on the selected date.');
        }

        // Format the date and times properly
        $attendance = new Attendance();
        $attendance->employee_id = $validated['employee_id'];
        $attendance->date = $validated['date'];
        $attendance->status = $validated['status'];
        $attendance->remarks = $validated['remarks'];
        
        if (!empty($validated['check_in'])) {
            $attendance->check_in = $validated['check_in'];
        }
        
        if (!empty($validated['check_out'])) {
            $attendance->check_out = $validated['check_out'];
        }
        
        $attendance->save();

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Show the form for editing the specified attendance record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        // dd($attendance);
        // return response()->json($attendance);
        $attendance = [
            'id' => $attendance->id,
            'employee_id' => $attendance->employee_id,
            'date' => $attendance->date->format('Y-m-d'),
            'check_in' => $attendance->check_in ? $attendance->check_in->format('H:i') : null,
            'check_out' => $attendance->check_out ? $attendance->check_out->format('H:i') : null,
            'status' => $attendance->status,
            'remarks' => $attendance->remarks
        ];
        
        return response()->json($attendance);
    }

    /**
     * Update the specified attendance record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $companyId = auth()->user()->company_id;
        
        // Find the attendance record and ensure it belongs to the user's company
        $attendance = Attendance::whereHas('employee', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->findOrFail($id);
        
        $validated = $request->validate([
            // 'employee_id' => [
            //     'required',
            //     'exists:employees,id',
            //     function ($attribute, $value, $fail) use ($companyId) {
            //         $employee = Employee::find($value);
            //         if ($employee && $employee->company_id !== $companyId) {
            //             $fail('The selected employee is invalid.');
            //         }
            //     },
            // ],
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after_or_equal:check_in',
            'status' => 'required|in:Present,Absent,Late,On Leave,Half Day',
            'remarks' => 'nullable|string|max:500',
        ]);

        // Format the date and times properly
        $attendance->date = $validated['date'];
        $attendance->status = $validated['status'];
        $attendance->remarks = $validated['remarks'];
        
        if (!empty($validated['check_in'])) {
            $attendance->check_in = $validated['check_in'];
        } else {
            $attendance->check_in = null;
        }
        
        if (!empty($validated['check_out'])) {
            $attendance->check_out = $validated['check_out'];
        } else {
            $attendance->check_out = null;
        }
        
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully'
        ]);
    }

    /**
     * Remove the specified attendance record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $companyId = auth()->user()->company_id;
            
            // Find the attendance record and ensure it belongs to the user's company
            $attendance = Attendance::withTrashed()
                ->whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->findOrFail($id);
            
            // Force delete if already soft deleted, otherwise soft delete
            if ($attendance->trashed()) {
                $attendance->forceDelete();
                $message = 'Attendance record permanently deleted successfully.';
            } else {
                $attendance->delete();
                $message = 'Attendance record moved to trash successfully.';
            }
            
            // Log the deletion
            \Log::info('Attendance deleted', [
                'attendance_id' => $id,
                'deleted_by' => auth()->id(),
                'deleted_at' => now(),
                'method' => request()->method()
            ]);
            
            // Return JSON response for AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting attendance: ' . $e->getMessage(), [
                'attendance_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete attendance: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete attendance: ' . $e->getMessage());
        }
    }

    /**
     * Export attendance records to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        // Start with base query that filters by company
        $query = Attendance::with(['employee.user', 'employee.department', 'employee.designation'])
            ->whereHas('employee', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        // Apply the same filters as the index method
        $filters = [];
        
        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            $startDate = Carbon::parse($dates[0])->startOfDay();
            $endDate = isset($dates[1]) 
                ? Carbon::parse($dates[1])->endOfDay() 
                : $startDate->copy()->endOfDay();
            
            $query->whereBetween('date', [$startDate, $endDate]);
            $filters[] = $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y');
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
            $employee = Employee::find($request->employee_id);
            if ($employee) {
                $filters[] = 'Employee: ' . ($employee->user->name ?? 'N/A');
            }
        }

        if ($request->filled('department_id')) {
            $department = Department::find($request->department_id);
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
            if ($department) {
                $filters[] = 'Department: ' . $department->name;
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
            $filters[] = 'Status: ' . $request->status;
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $exportType = $request->get('type', 'excel');
        $fileName = 'attendance-export-' . now()->format('Y-m-d') . ($exportType === 'pdf' ? '.pdf' : '.xlsx');

        if ($exportType === 'pdf') {
            $pdf = PDF::loadView('admin.attendance.exports.pdf', [
                'attendances' => $attendances,
                'filters' => $filters,
                'date' => now()->format('d M, Y')
            ]);
            
            return $pdf->download($fileName);
        }
        
        // Default to Excel export
        return Excel::download(
            new AdminAttendanceExport($attendances, $filters), 
            $fileName
        );
    }

    /**
     * Import attendance records from Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'overwrite_existing' => 'boolean'
        ]);

        try {
            $overwrite = $request->boolean('overwrite_existing', false);
            
            // Get the file
            $file = $request->file('file');
            
            // Read the file
            $data = Excel::toArray([], $file)[0];
            
            if (count($data) <= 1) { // First row is header
                return redirect()->back()
                    ->with('error', 'The uploaded file is empty.');
            }
            
            $header = array_shift($data);
            $requiredHeaders = ['employee_id', 'date', 'status'];
            
            // Validate headers
            foreach ($requiredHeaders as $required) {
                if (!in_array(strtolower($required), array_map('strtolower', $header))) {
                    return redirect()->back()
                        ->with('error', "The uploaded file is missing required column: {$required}");
                }
            }
            
            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];
            
            // Pre-fetch all valid employee IDs for this company
            $validEmployeeIds = Employee::where('company_id', $companyId)
                ->pluck('id')
                ->toArray();
            
            foreach ($data as $index => $row) {
                try {
                    $row = array_combine($header, $row);
                    
                    // Basic validation
                    $validator = Validator::make($row, [
                        'employee_id' => 'required|exists:employees,id',
                        'date' => 'required|date',
                        'check_in' => 'nullable|date_format:H:i',
                        'check_out' => 'nullable|date_format:H:i|after_or_equal:check_in',
                        'status' => 'required|in:Present,Absent,Late,On Leave,Half Day',
                        'remarks' => 'nullable|string|max:500',
                    ]);
                    
                    if ($validator->fails()) {
                        $errors[] = "Row " . ($index + 2) . ": " . implode(' ', $validator->errors()->all());
                        continue;
                    }
                    
                    // Check if employee belongs to the company
                    if (!in_array($row['employee_id'], $validEmployeeIds)) {
                        $skipped++;
                        $errors[] = "Row " . ($index + 2) . ": Employee ID {$row['employee_id']} does not belong to your company.";
                        continue;
                    }
                    
                    // Prepare data
                    $attendanceData = [
                        'employee_id' => $row['employee_id'],
                        'date' => $row['date'],
                        'status' => $row['status'],
                        'check_in' => $row['check_in'] ?? null,
                        'check_out' => $row['check_out'] ?? null,
                        'remarks' => $row['remarks'] ?? null,
                    ];
                    
                    // Check if record exists
                    $existing = Attendance::where('employee_id', $attendanceData['employee_id'])
                        ->whereDate('date', $attendanceData['date'])
                        ->first();
                    
                    if ($existing) {
                        if ($overwrite) {
                            $existing->update($attendanceData);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        Attendance::create($attendanceData);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    continue;
                }
            }
            
            $message = "Successfully imported {$imported} records";
            if ($updated > 0) {
                $message .= " and updated {$updated} records";
            }
            $message .= ".";
            
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " records had errors.";
                
                // Store errors in session
                return redirect()->back()
                    ->with('warning', $message)
                    ->with('import_errors', $errors);
            }
            
            return redirect()->route('admin.attendance.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Download import template.
     *
     * @return \Illuminate\Http\Response
     */
    public function template()
    {
        $fileName = 'attendance-import-template-' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(
            new AttendanceImportTemplate(), 
            $fileName
        );
    }

    /**
     * Display attendance summary by department.
     *
     * @return \Illuminate\Http\Response
     */
    public function summary()
    {
        $companyId = auth()->user()->company_id;
        $today = Carbon::today();
        $todayFormatted = $today->toDateString();
        
        // Get department summary for the current company
        $departmentSummary = Department::with(['employees' => function($query) use ($todayFormatted, $companyId) {
            $query->where('employees.company_id', $companyId)
                ->with(['user', 'designation'])
                ->with(['attendances' => function($q) use ($todayFormatted) {
                    $q->whereDate('date', $todayFormatted);
                }])
                ->orderBy('name');
        }])
        ->where('departments.company_id', $companyId)
        ->select('departments.id', 'departments.name')
        ->selectRaw('COUNT(DISTINCT employees.id) as total_employees')
        ->selectRaw('COUNT(CASE WHEN attendances.status = "Present" THEN 1 END) as present_count')
        ->selectRaw('COUNT(CASE WHEN attendances.status = "Absent" THEN 1 END) as absent_count')
        ->selectRaw('COUNT(CASE WHEN attendances.status = "Late" THEN 1 END) as late_count')
        ->leftJoin('employees', function($join) use ($companyId) {
            $join->on('departments.id', '=', 'employees.department_id')
                ->where('employees.company_id', $companyId);
        })
        ->leftJoin('attendances', function($join) use ($todayFormatted) {
            $join->on('employees.id', '=', 'attendances.employee_id')
                ->where('attendances.date', $todayFormatted);
        })
        ->groupBy('departments.id', 'departments.name')
        ->orderBy('departments.name')
        ->get();
        
        // Calculate summary for all departments
        $totalEmployees = $departmentSummary->sum('total_employees');
        $totalPresent = $departmentSummary->sum('present_count');
        $totalAbsent = $departmentSummary->sum('absent_count');
        $totalLate = $departmentSummary->sum('late_count');
        
        return view('admin.attendance.summary', compact(
            'departmentSummary',
            'today',
            'totalEmployees',
            'totalPresent',
            'totalAbsent',
            'totalLate'
        ));
    }
}
