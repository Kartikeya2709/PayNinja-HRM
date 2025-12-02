<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendanceSettingController extends Controller
{
    /**
     * Display the attendance settings form.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;

        // Only fetch settings for the current user's company
        $settings = AttendanceSetting::where('company_id', $companyId)
            ->latest('updated_at')
            ->withoutGlobalScopes()
            ->first();

        // Set default values if settings don't exist
        if (!$settings) {
            $settings = new AttendanceSetting([
                'grace_period' => '00:15:00',
                'work_hours' => 8,
                'auto_absent_time' => '18:00:00',
                'enable_geolocation' => true,
                'office_latitude' => '28.629402',
                'office_longitude' => '77.165363',
                'geofence_radius' => 100,
                'checkin_methods' => 'both',
                'company_id' => $companyId,
                'weekend_days' => json_encode(['Saturday', 'Sunday', 'saturday_1_3', 'saturday_2_4', 'saturday_1_3_5']),
                'created_by' => Auth::id()
            ]);
            $settings->save();
        }

        // Only fetch the current company, not all companies
        $company = Company::findOrFail($companyId);

        // Get all departments and employees for the company
        $departments = Department::where('company_id', $companyId)->get();
        $employees = Employee::where('company_id', $companyId)
            ->with('department') // Eager load department relationship
            ->get();

        // Load existing exemptions if settings exist
        if ($settings) {
            $settings->load(['exemptedDepartments', 'exemptedEmployees']);
        }

        return view('admin.attendance.settings', [
            'settings' => $settings,
            'company' => $company,
            'companies' => collect([$company]),
            'departments' => $departments,
            'employees' => $employees,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the attendance settings in a read-only view.
     */
    public function show()
    {
        $companyId = Auth::user()->company_id;

        // Only fetch settings for the current user's company
        $settings = AttendanceSetting::where('company_id', $companyId)
            ->with(['exemptedDepartments', 'exemptedEmployees'])
            ->latest('updated_at')
            ->withoutGlobalScopes()
            ->first();

        if (!$settings) {
            return redirect()->route('admin-attendance.settings')
                ->with('info', 'No attendance settings found. Please configure them first.');
        }

        // Get the current company
        $company = Company::findOrFail($companyId);

        return view('admin.attendance.show', [
            'settings' => $settings,
            'company' => $company
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttendanceSetting $attendanceSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the attendance settings including geolocation exemptions.
     */
    public function update(Request $request, $id = null)
    {

        try {
            // Check if this is an AJAX request
            $isAjax = $request->ajax() || $request->wantsJson();

            // Get the authenticated user's company ID
            $userCompanyId = Auth::user()->company_id;

            // Prepare the data for validation
            $data = $request->all();

            // Ensure the user can only update settings for their own company
            if (isset($data['company_id']) && $data['company_id'] != $userCompanyId) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized: You can only update settings for your own company.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized: You can only update settings for your own company.');
            }

            // Set the company_id to the user's company to prevent tampering
            $data['company_id'] = $userCompanyId;
            \Log::debug('Processed request data:', $data);

            // Convert weekend_days from JSON string to array if needed
            if (isset($data['weekend_days'])) {
                if (is_string($data['weekend_days'])) {
                    // Remove any escaping from the JSON string
                    $weekendDaysString = stripslashes($data['weekend_days']);
                    \Log::debug('weekend_days is a string, attempting to decode:', ['input' => $data['weekend_days'], 'stripped' => $weekendDaysString]);

                    $decoded = json_decode($weekendDaysString, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data['weekend_days'] = $decoded;
                        \Log::debug('Successfully decoded JSON weekend_days:', $data['weekend_days']);
                    } else {
                        // If it's not a valid JSON, treat it as a comma-separated string
                        $data['weekend_days'] = array_filter(
                            array_map('trim', explode(',', $weekendDaysString))
                        );
                        \Log::debug('Treated as comma-separated string, result:', $data['weekend_days']);
                    }
                } else {
                    \Log::debug('weekend_days is already an array:', $data['weekend_days']);
                }
            } else {
                $data['weekend_days'] = [];
            }

            // Log data before validation
            \Log::debug('Data before validation:', $data);

            // Validate the request data
            $validator = \Validator::make($data, [
                'company_id' => 'required|exists:companies,id',
                'office_start_time' => 'required|date_format:H:i',
                'office_end_time' => 'required|date_format:H:i|after:office_start_time',
                'work_hours' => 'required|numeric|min:1|max:24',
                'grace_period' => 'required|date_format:H:i',
                'auto_absent_time' => 'nullable|date_format:H:i',
                'allow_multiple_check_in' => 'sometimes|boolean',
                'track_location' => 'sometimes|boolean',
                'enable_geolocation' => 'sometimes|boolean',
                'office_latitude' => 'nullable|required_if:enable_geolocation,1|numeric|between:-90,90',
                'office_longitude' => 'nullable|required_if:enable_geolocation,1|numeric|between:-180,180',
                'geofence_radius' => 'nullable|required_if:enable_geolocation,1|numeric|min:20|max:1000',
                'checkin_methods' => 'required|in:app,web,both',
                'weekend_days' => 'sometimes|array',
                // Exemption fields
                'exempted_departments' => 'sometimes|array',
                'exempted_departments.*' => 'integer|exists:departments,id',
                'exempted_employees' => 'sometimes|array',
                'exempted_employees.*' => 'integer|exists:employees,id',
            ]);

            // Convert boolean fields
            $validated = $request->only([
                'company_id',
                'office_start_time',
                'office_end_time',
                'work_hours',
                'grace_period',
                'auto_absent_time',
                'allow_multiple_check_in',
                'track_location',
                'enable_geolocation',
                'office_latitude',
                'office_longitude',
                'geofence_radius',
                'checkin_methods'
            ]);

            $validated['allow_multiple_check_in'] = $request->boolean('allow_multiple_check_in');
            $validated['track_location'] = $request->boolean('track_location');
            $validated['enable_geolocation'] = $request->boolean('enable_geolocation');

            // Handle geolocation settings
            if ($validated['enable_geolocation']) {
                $validated['geofence_radius'] = min(max($request->input('geofence_radius', 100), 50), 1000);
                $validated['office_latitude'] = $request->input('office_latitude');
                $validated['office_longitude'] = $request->input('office_longitude');
            } else {
                $validated['office_latitude'] = null;
                $validated['office_longitude'] = null;
                $validated['geofence_radius'] = 100;
            }

            // Process weekend days - at this point it should already be an array from validation
            $validWeekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            // Ensure auto_absent_time is properly formatted
            if (!empty($validated['auto_absent_time']) && strpos($validated['auto_absent_time'], ':') !== false) {
                $validated['auto_absent_time'] = \Carbon\Carbon::createFromFormat('H:i', $validated['auto_absent_time'])->format('H:i:s');
            }

            \Log::debug('Processing weekend_days. Raw value:', ['value' => $validated['weekend_days'] ?? null]);

            $validated['weekend_days'] = array_values(array_unique(
                array_filter((array)($validated['weekend_days'] ?? []), function($day) use ($validWeekDays) {
                    $day = trim($day);
                    $isValid = in_array($day, $validWeekDays);
                    if (!$isValid) {
                        \Log::warning("Invalid day found in weekend_days: " . $day);
                    }
                    return $isValid;
                })
            ));

            \Log::debug('Processed weekend_days:', $validated['weekend_days']);

            // Encode the array to JSON for storage
            $validated['weekend_days'] = json_encode($validated['weekend_days']);
            \Log::debug('Encoded weekend_days for storage:', ['encoded' => $validated['weekend_days']]);

            // Set created_by if it's a new record
            if (empty($id)) {
                $validated['created_by'] = Auth::id();
            }

            // Validate the data
            if ($validator->fails()) {
                \Log::error('Validation failed', ['errors' => $validator->errors()]);
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();

            // Process weekend days
            $validWeekDays = [
                'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday',
                'saturday_1_3', 'saturday_2_4', 'saturday_1_3_5'
            ];
            $validated['weekend_days'] = array_values(array_unique(
                array_filter((array)($validated['weekend_days'] ?? []), function($day) use ($validWeekDays) {
                    $day = trim($day);
                    return in_array($day, $validWeekDays);
                })
            ));
            // Encode the array to JSON for storage
            $validated['weekend_days'] = json_encode($validated['weekend_days']);

            // Ensure we're only updating settings for the user's company
            $settings = null;

            // Persist settings and sync exemptions in a transaction
            \DB::beginTransaction();
            try {
                if ($id) {
                    // For updates, first find the setting and verify it belongs to the user's company
                    $settings = AttendanceSetting::where('id', $id)
                        ->where('company_id', $userCompanyId)
                        ->first();

                    if (!$settings) {
                        if ($isAjax) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Settings not found or you do not have permission to update them.'
                            ], 404);
                        }
                        return redirect()->back()->with('error', 'Settings not found or you do not have permission to update them.');
                    }

                    // Update existing settings
                    $settings->update($validated);
                    $settings->updated_by = Auth::id();
                    $settings->save();
                } else {
                    // Create new settings for the company
                    $validated['created_by'] = Auth::id();
                    $validated['updated_by'] = Auth::id();
                    $settings = AttendanceSetting::create($validated);
                }

                // Sync exemptions if provided
                $departmentIds = (array)($data['exempted_departments'] ?? []);
                $employeeIds = (array)($data['exempted_employees'] ?? []);

                if (!empty($departmentIds)) {
                    $settings->exemptedDepartments()->sync($departmentIds);
                } else {
                    // If no departments provided, detach all to reflect clearing selection
                    $settings->exemptedDepartments()->detach();
                }

                if (!empty($employeeIds)) {
                    $settings->exemptedEmployees()->sync($employeeIds);
                } else {
                    $settings->exemptedEmployees()->detach();
                }

                \DB::commit();
            } catch (\Throwable $t) {
                \DB::rollBack();
                throw $t;
            }

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Office timings updated successfully',
                    'data' => $settings
                ]);
            }

            return redirect()->route('admin-attendance.settings')
                ->with('success', 'Office timings updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating settings: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error updating settings: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceSetting $attendanceSetting)
    {
        //
    }
}
