<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\API\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseApiController
{
    /**
     * Get the authenticated employee's profile
     */
    public function getProfile()
    {
        try {
            $employee = Auth::user()->employee;
            $profile = [
                'personal_info' => [
                    'id' => $employee->user_id,
                    'name' => $employee->name,
                    'email' => $employee->user->email,
                    'phone' => $employee->phone,
                    'date_of_birth' => $employee->dob,
                    'gender' => $employee->gender,
                ],
                'employment_info' => [
                    'company' => $employee->company ? $employee->company->name : null,
                    'employee_id' => $employee->id,
                    'department' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation ? $employee->designation->title : null,
                    'joining_date' => $employee->joining_date,
                    'employment_type' => $employee->employment_type,
                ],
                'reporting_info' => [
                    'reporting_manager' => $employee->reportingManager ? [
                        'id' => $employee->reportingManager->id,
                        'name' => $employee->reportingManager->name,
                    ] : null,
                ],
            ];

            return $this->sendResponse($profile, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving profile', [$e->getMessage()], 500);
        }
    }

    /**
     * Update employee's basic information
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'nullable|string|max:20',
                'emergency_contact' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error' , [$validator->errors()] ,422);
            }

            $employee = Auth::user()->employee;
            $employee->update($validator->validated());

            return $this->sendResponse(
                ['employee' => $employee],
                'Profile updated successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error updating profile', [$e->getMessage()], 500);
        }
    }

    /**
     * Get list of colleagues from the same company
     */
    public function getColleagues(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'search' => 'nullable|string|max:100',
                'department_id' => 'nullable|exists:departments,id',
                'designation_id' => 'nullable|exists:designations,id',
                'sort_by' => 'nullable|in:name,employee_code,joining_date',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
            }

            $currentUser = Auth::user()->load('employee.company');
            $colleagues = collect();
            $companyInfo = null;

            if ($currentUser->employee && $currentUser->employee->company) {
                $company = $currentUser->employee->company;
                $companyInfo = [
                    'id' => $company->id,
                    'name' => $company->name
                ];

                $query = Employee::with(['user:id,name,email', 'department:id,name', 'designation:id,title'])
                    ->where('company_id', $company->id)
                    ->where('id', '!=', $currentUser->employee->id);

                // Search filter
                if ($request->search) {
                    $searchTerm = $request->search;
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('employee_code', 'like', '%' . $searchTerm . '%')
                          ->orWhereHas('user', function($sq) use ($searchTerm) {
                              $sq->where('email', 'like', '%' . $searchTerm . '%');
                          });
                    });
                }

                // Department filter
                if ($request->department_id) {
                    $query->where('department_id', $request->department_id);
                }

                // Designation filter
                if ($request->designation_id) {
                    $query->where('designation_id', $request->designation_id);
                }

                // Sorting
                $sortBy = $request->get('sort_by', 'name');
                $sortOrder = $request->get('sort_order', 'asc');
                $query->orderBy($sortBy, $sortOrder);

                // Pagination
                $perPage = $request->get('per_page', 10);
                $colleagues = $query->paginate($perPage);

                // Transform the data
                $colleagues->getCollection()->transform(function ($colleague) {
                    return [
                        'id' => $colleague->id,
                        'name' => $colleague->name,
                        'email' => $colleague->user->email,
                        'employee_code' => $colleague->employee_code,
                        'department' => $colleague->department ? [
                            'id' => $colleague->department->id,
                            'name' => $colleague->department->name
                        ] : null,
                        'designation' => $colleague->designation ? [
                            'id' => $colleague->designation->id,
                            'title' => $colleague->designation->title
                        ] : null,
                        'joining_date' => $colleague->joining_date
                    ];
                });
            }

            return $this->sendResponse([
                'company' => $companyInfo,
                'total_colleagues' => $colleagues->total(),
                'colleagues' => $colleagues->items(),
                'pagination' => [
                    'current_page' => $colleagues->currentPage(),
                    'per_page' => $colleagues->perPage(),
                    'total_pages' => $colleagues->lastPage(),
                    'total_records' => $colleagues->total()
                ]
            ], 'Colleagues retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving colleagues', [$e->getMessage()], 500);
        }
    }
}