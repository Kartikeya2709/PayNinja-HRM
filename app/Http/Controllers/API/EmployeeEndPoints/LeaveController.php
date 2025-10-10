<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\API\BaseApiController;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Holiday;
use App\Models\AcademicHoliday;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveController extends BaseApiController
{
    /**
     * Check if a date is a weekend based on company settings
     *
     * @param \Carbon\Carbon $date
     * @return bool
     */
    protected function isWeekend($date)
    {
        $companyId = Auth::user()->employee->company_id;
        // Get company's weekend configuration
        $settings = AttendanceSetting::where('company_id', $companyId)
            ->latest('updated_at')
            ->first();

        if (!$settings) {
            // Default to Saturday and Sunday if no settings found
            return in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
        }

        // Decode the JSON string to an array
        $weekendDays = json_decode($settings->weekend_days, true) ?? ['Sunday'];

        // If it's not an array, convert it to an array
        if (!is_array($weekendDays)) {
            $weekendDays = [$weekendDays];
        }

        $dayOfWeek = $date->format('l'); // Full day name (e.g., 'Monday')
        $dayOfMonth = $date->day;
        $weekNumber = (int)ceil($dayOfMonth / 7); // Get week number in month (1-5)
        $isSaturday = (strtolower($dayOfWeek) === 'saturday');

        foreach ($weekendDays as $pattern) {
            $pattern = trim($pattern);

            // Handle simple day names (e.g., "Sunday", "Saturday")
            if (in_array(ucfirst($pattern), ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])) {
                if (strtolower($pattern) === strtolower($dayOfWeek)) {
                    return true;
                }
                continue;
            }

            // Handle patterns like "1st & 3rd Saturday", "2nd & 4th Saturday", "1st, 3rd & 5th Saturday"
            if (preg_match('/(\d+)(?:st|nd|rd|th)(?:\s*[,&]\s*(\d+)(?:st|nd|rd|th))*(?:\s*&\s*(\d+)(?:st|nd|rd|th))?\s+([a-z]+)/i', $pattern, $matches)) {
                $day = ucfirst($matches[4]); // e.g., "Saturday"
                $weeks = array_slice($matches, 1, -1); // Get all week numbers
                $weeks = array_filter($weeks, 'is_numeric'); // Filter out non-numeric values
                $weeks = array_map('intval', $weeks); // Convert to integers

                // Check if current day matches and is one of the specified weeks
                if (strtolower($day) === strtolower($dayOfWeek) && in_array($weekNumber, $weeks)) {
                    return true;
                }
                continue;
            }

            // Handle simple patterns like "saturday_2_4" (backward compatibility)
            if (preg_match('/^([a-z]+)_(\d+)(?:_(\d+))?$/i', $pattern, $matches)) {
                $day = ucfirst($matches[1]);
                $week1 = (int)$matches[2];
                $week2 = isset($matches[3]) ? (int)$matches[3] : null;

                if (strtolower($day) === strtolower($dayOfWeek)) {
                    if ($weekNumber === $week1 || ($week2 !== null && $weekNumber === $week2)) {
                        return true;
                    }
                }
                continue;
            }
        }

        return false;
    }

    /**
     * Check if a date is a holiday
     *
     * @param Carbon $date
     * @param int $companyId
     * @return bool
     */
    protected function isHoliday($date, $companyId)
    {
        return AcademicHoliday::where('company_id', $companyId)
            ->whereDate('from_date', '<=', $date->format('Y-m-d'))
            ->whereDate('to_date', '>=', $date->format('Y-m-d'))
            ->exists();
    }

    /**
     * Check if employee has overlapping leave requests
     *
     * @param int $employeeId
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    protected function hasOverlappingLeaves($employeeId, $startDate, $endDate)
    {
        return LeaveRequest::where('employee_id', $employeeId)
            ->whereIn('status', ['approved', 'pending'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($innerQ) use ($startDate, $endDate) {
                      $innerQ->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                  });
            })
            ->exists();
    }

    /**
     * Check if employee has a leave request with the same start date
     *
     * @param int $employeeId
     * @param string $startDate
     * @return bool
     */
    protected function hasSameStartDateLeave($employeeId, $startDate)
    {
        return LeaveRequest::where('employee_id', $employeeId)
            ->where('start_date', $startDate)
            ->whereIn('status', ['approved', 'pending'])
            ->exists();
    }

    /**
     * Get leave balance
     */
    public function getLeaveBalance()
    {
        try {
            $employee = Auth::user()->employee;

            // Get leave balances for the employee with their leave types
            $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                ->with([
                    'leaveType' => function ($query) use ($employee) {
                        $query->where('company_id', $employee->company_id)
                            ->where('is_active', true)
                            ->select('id', 'name', 'default_days', 'description');
                    }
                ])
                ->get()
                ->map(function ($balance) {
                    return [
                        'id' => $balance->id,
                        'leave_type' => [
                            'id' => $balance->leaveType->id,
                            'name' => $balance->leaveType->name,
                            'default_days' => $balance->leaveType->default_days,
                            'description' => $balance->leaveType->description
                        ],
                        'total_days' => $balance->total_days,
                        'used_days' => $balance->used_days,
                        'remaining_days' => $balance->total_days - $balance->used_days
                    ];
                });

            // Calculate summary
            $summary = [
                'total_leave_days' => $leaveBalance->sum('total_days'),
                'total_used_days' => $leaveBalance->sum('used_days'),
                'total_remaining_days' => $leaveBalance->sum(function ($balance) {
                    return $balance['total_days'] - $balance['used_days'];
                })
            ];

            return $this->sendResponse([
                'leave_balance' => $leaveBalance,
                'summary' => $summary
            ], 'Leave balance retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving leave balance', [$e->getMessage()], 500);
        }
    }

    /**
     * Apply for leave
     */
    public function applyLeave(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'leave_type_id' => 'required|exists:leave_types,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
            }

            $employee = Auth::user()->employee;

            // Parse dates
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Check for overlapping leave requests
            if ($this->hasOverlappingLeaves($employee->id, $request->start_date, $request->end_date)) {
                return $this->sendError('You already have an approved or pending leave request that overlaps with these dates.', [], 400);
            }

            // Check for duplicate start date
            if ($this->hasSameStartDateLeave($employee->id, $request->start_date)) {
                return $this->sendError('You already have a pending or approved leave request starting on this date.', [], 400);
            }

            // Get all dates in the leave period
            $period = CarbonPeriod::create($startDate, $endDate);

            $workingDays = [];
            $weekendDays = [];
            $holidayDates = [];

            // Categorize each day in the period
            foreach ($period as $date) {
                if ($this->isHoliday($date, $employee->company_id)) {
                    $holidayDates[] = $date->format('Y-m-d');
                } elseif ($this->isWeekend($date)) {
                    $weekendDays[] = $date->format('Y-m-d');
                } else {
                    $workingDays[] = $date->format('Y-m-d');
                }
            }

            $totalWorkingDays = count($workingDays);
            $totalCalendarDays = $startDate->diffInDays($endDate) + 1;

            if ($totalWorkingDays <= 0) {
                return $this->sendError('The selected date range only includes weekends and/or holidays. No leave days will be deducted.', [], 400);
            }

            // Check leave balance
            $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $request->leave_type_id)
                ->first();

            if (!$leaveBalance) {
                return $this->sendError('Leave balance not found for this leave type', [], 400);
            }

            $remainingDays = $leaveBalance->total_days - $leaveBalance->used_days;

            if ($totalWorkingDays > $remainingDays) {
                return $this->sendError('Insufficient leave balance', [
                    'requested_days' => $totalWorkingDays,
                    'remaining_days' => $remainingDays,
                    'leave_type' => $leaveBalance->leaveType->name
                ], 400);
            }

            // Create leave request
            $leave = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalWorkingDays,
                'working_days' => $workingDays,
                'weekend_days' => $weekendDays,
                'holiday_days' => $holidayDates,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            return $this->sendResponse(
                $leave,
                'Leave application submitted successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error applying for leave', [$e->getMessage()], 500);
        }
    }
    /**
     * Cancel leave application
     */
    public function cancelLeave($id)
    {
        try {
            $leave = LeaveRequest::findOrFail($id)->where('employee_id', Auth::user()->employee->id)->first();

            if ($leave->status !== 'pending') {
                return $this->sendError('Only pending leaves can be cancelled', [], 400);
            }

            $leave->update(['status' => 'cancelled']);

            return $this->sendResponse(
                ['leave' => $leave],
                'Leave application cancelled successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error cancelling leave', [$e->getMessage()], 500);
        }
    }

    /**
     * Get all leave requests with filters
     */
    public function getLeaveRequests(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'year' => 'nullable|integer|min:2000',
                'month' => 'nullable|integer|between:1,12',
                'status' => 'nullable|in:pending,approved,rejected,cancelled',
                'leave_type_id' => 'nullable|exists:leave_types,id',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
                'search' => 'nullable|string|max:100',
                'sort_by' => 'nullable|in:created_at,start_date,status',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
            }

            $query = LeaveRequest::where('employee_id', Auth::user()->employee->id)
                ->with([
                    'leaveType:id,name,default_days',
                    'approver:id,name'
                ]);

            // Date range filter
            if ($request->from_date && $request->to_date) {
                $query->where(function ($q) use ($request) {
                    $q->whereBetween('start_date', [
                        Carbon::parse($request->from_date)->startOfDay(),
                        Carbon::parse($request->to_date)->endOfDay()
                    ])->orWhereBetween('end_date', [
                                Carbon::parse($request->from_date)->startOfDay(),
                                Carbon::parse($request->to_date)->endOfDay()
                            ]);
                });
            } else {
                // Year and month filter
                if ($request->year) {
                    $query->whereYear('start_date', $request->year);
                }
                if ($request->month) {
                    $query->whereMonth('start_date', $request->month);
                }
            }

            // Status filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Leave type filter
            if ($request->leave_type_id) {
                $query->where('leave_type_id', $request->leave_type_id);
            }

            // Search in reason
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('reason', 'like', '%' . $request->search . '%')
                        ->orWhere('contact_number', 'like', '%' . $request->search . '%');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $leaves = $query->paginate($perPage);

            // Transform the data
            $leaves->getCollection()->transform(function ($leave) {
                return [
                    'id' => $leave->id,
                    'leave_type' => $leave->leaveType ? [
                        'id' => $leave->leaveType->id,
                        'name' => $leave->leaveType->name,
                        'default_days' => $leave->leaveType->default_days
                    ] : null,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'number_of_days' => $leave->number_of_days,
                    'reason' => $leave->reason,
                    'status' => $leave->status,
                    'approver' => $leave->approver ? [
                        'id' => $leave->approver->id,
                        'name' => $leave->approver->name
                    ] : null,
                    'created_at' => $leave->created_at,
                    'updated_at' => $leave->updated_at
                ];
            });

            return $this->sendResponse([
                'leaves' => $leaves->items(),
                'pagination' => [
                    'current_page' => $leaves->currentPage(),
                    'last_page' => $leaves->lastPage(),
                    'per_page' => $leaves->perPage(),
                    'total' => $leaves->total()
                ],
                'summary' => [
                    'total_requests' => LeaveRequest::where('employee_id', Auth::user()->employee->id)->count(),
                    'pending_requests' => LeaveRequest::where('employee_id', Auth::user()->employee->id)->where('status', 'pending')->count(),
                    'approved_requests' => LeaveRequest::where('employee_id', Auth::user()->employee->id)->where('status', 'approved')->count(),
                    'rejected_requests' => LeaveRequest::where('employee_id', Auth::user()->employee->id)->where('status', 'rejected')->count(),
                    'cancelled_requests' => LeaveRequest::where('employee_id', Auth::user()->employee->id)->where('status', 'cancelled')->count()
                ]
            ], 'Leave requests retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving leave requests', [$e->getMessage()], 500);
        }
    }

    public function getLeaveTypes()
    {
        try {
            $employee = Auth::user()->employee;

            // Get leave types through leave balances for the logged-in employee
            $leaveTypes = LeaveType::whereHas('leaveBalances', function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            })
                ->where('company_id', $employee->company_id)
                ->where('is_active', true)
                ->with([
                    'leaveBalances' => function ($query) use ($employee) {
                        $query->where('employee_id', $employee->id)
                            ->select('id', 'leave_type_id', 'total_days', 'used_days');
                    }
                ])
                ->get()
                ->map(function ($leaveType) {
                    $balance = $leaveType->leaveBalances->first();
                    return [
                        'id' => $leaveType->id,
                        'name' => $leaveType->name,
                        'default_days' => $leaveType->default_days,
                        'description' => $leaveType->description,
                        'balance' => [
                            'total_days' => $balance->total_days,
                            'used_days' => $balance->used_days,
                            'remaining_days' => $balance->total_days - $balance->used_days
                        ]
                    ];
                });

            return $this->sendResponse([
                'leave_types' => $leaveTypes,
                'summary' => [
                    'total_leave_types' => $leaveTypes->count(),
                    'total_days_allocated' => $leaveTypes->sum('balance.total_days'),
                    'total_days_used' => $leaveTypes->sum('balance.used_days'),
                    'total_days_remaining' => $leaveTypes->sum(function ($type) {
                        return $type['balance']['remaining_days'];
                    })
                ]
            ], 'Leave types retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving leave types', [$e->getMessage()], 500);
        }
    }
}
