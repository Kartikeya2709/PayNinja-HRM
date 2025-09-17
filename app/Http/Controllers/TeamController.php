<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,company_admin']);
    }

    public function index()
    {
        $teams = Team::with(['department', 'members'])
            ->where('company_id', auth()->user()->company_id)
            ->get();
        
        return view('company.teams.index', compact('teams'));
    }

    public function create()
    {
        $departments = Department::where('company_id', auth()->user()->company_id)->get();
        return view('company.teams.create', compact('departments'));
    }

    public function getEmployeesByDepartment($departmentId)
    {
        $employees = Employee::where('company_id', auth()->user()->company_id)
            ->where('department_id', $departmentId)
            ->with('designation')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'designation' => $employee->designation->title
                ];
            });

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,NULL,id,company_id,' . auth()->user()->company_id . ',department_id,' . $request->department_id,
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'reporter_id' => 'required|exists:employees,id',
            'reportee_ids' => 'required|array|min:1',
            'reportee_ids.*' => 'exists:employees,id'
        ]);

        $team = Team::create([
            'company_id' => auth()->user()->company_id,
            'department_id' => $request->department_id,
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id()
        ]);

        // Add reporter
        $team->members()->attach($request->reporter_id, [
            'role' => 'reporter',
            'assigned_by' => auth()->id()
        ]);

        // Add reportees
        foreach ($request->reportee_ids as $reporteeId) {
            $team->members()->attach($reporteeId, [
                'role' => 'reportee',
                'assigned_by' => auth()->id()
            ]);
        }

        return redirect()->route('company.teams.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Team created successfully.');
    }

    public function edit(Team $team)
    {
        if ($team->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $departments = Department::where('company_id', auth()->user()->company_id)->get();
        $employees = Employee::where('company_id', auth()->user()->company_id)->get();
        $reporters = $team->reporters()->pluck('employee_id')->toArray();
        $reportees = $team->reportees()->pluck('employee_id')->toArray();

        return view('company.teams.edit', compact('team', 'departments', 'employees', 'reporters', 'reportees'));
    }

    public function update(Request $request, Team $team)
    {
        if ($team->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id . ',id,company_id,' . auth()->user()->company_id . ',department_id,' . $request->department_id,
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'reporter_id' => 'required|exists:employees,id',
            'reportee_ids' => 'required|array|min:1',
            'reportee_ids.*' => 'exists:employees,id'
        ]);

        $team->update([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'description' => $request->description
        ]);

        // Reset team members
        $team->members()->detach();

        // Add reporter
        $team->members()->attach($request->reporter_id, [
            'role' => 'reporter',
            'assigned_by' => auth()->id()
        ]);

        // Add reportees
        foreach ($request->reportee_ids as $reporteeId) {
            $team->members()->attach($reporteeId, [
                'role' => 'reportee',
                'assigned_by' => auth()->id()
            ]);
        }

        return redirect()->route('company.teams.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        if ($team->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $team->members()->detach();
        $team->delete();

        return redirect()->route('company.teams.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Team deleted successfully.');
    }
}
