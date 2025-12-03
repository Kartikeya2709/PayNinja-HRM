<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\EmployeeIdPrefix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function listColleagues()
    {
        $currentUser = Auth::user()->load('employeeCompany');
        $colleagues = collect(); // Default to an empty collection
        $companyName = 'N/A';

        // Get company through employee relationship if it exists
        if ($currentUser->employeeCompany) {
            $companyName = $currentUser->employeeCompany->name;
            $colleagues = User::whereHas('employee', function($query) use ($currentUser) {
                $query->where('company_id', $currentUser->employee->company_id);
            })->with('employee')
            ->orderBy('name')
            ->get();
        }
        // Fallback to direct company relationship
        elseif ($currentUser->company_id) {
            $company = $currentUser->company;
            if ($company) {
                $companyName = $company->name;
                $colleagues = User::where('company_id', $currentUser->company_id)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('employee.colleagues', compact('colleagues', 'currentUser', 'companyName'));
    }
}
