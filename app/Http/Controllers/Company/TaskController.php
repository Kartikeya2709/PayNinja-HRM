<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\Employee;
use App\Models\Company;
use App\Models\TaskExtensionRequest;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,company_admin,employee', 'ensure.company']);
    }

    public function index(Request $request)
    {
        $company = Auth::user()->company;
        $query = Task::with(['assignedToMany', 'assignedBy', 'teamLead'])
            ->forCompany($company->id)
            ->orderBy('due_at');

        // Normalize filters and allow special value 'me' for assigned_to
        $currentEmployee = Auth::user()->employee ?? null;
        $filters = $request->only(['status', 'priority', 'assigned_to', 'assigned_by']);

        // Auto-apply assigned_to=me for non-admin employees on bare /tasks route
        $user = Auth::user();
        $isAdmin = $user->hasRole(['admin', 'company_admin']);
        if (!$isAdmin && $currentEmployee && !$request->has('assigned_to') && !$request->has('assigned_by') && !$request->has('status') && !$request->has('priority')) {
            $filters['assigned_to'] = $currentEmployee->id;
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Normalize assigned_to filter (support 'me', single id, comma-separated or array)
        $assignedToFilter = $filters['assigned_to'] ?? null;
        if ($assignedToFilter === 'me' && $currentEmployee) {
            $assignedToFilter = $currentEmployee->id;
        }

        if ($assignedToFilter) {
            if (!is_array($assignedToFilter)) {
                // allow comma separated string
                $assignedToFilter = explode(',', (string) $assignedToFilter);
            }

            // cast values to ints and remove empties
            $ids = collect($assignedToFilter)->map(function ($v) {
                return is_numeric($v) ? (int) $v : null; // @phpstan-ignore-line
            })->filter()->values()->all();

            if (!empty($ids)) {
                $query->whereHas('assignedToMany', function ($q) use ($ids) {
                    $q->whereIn('employees.id', $ids);
                });
                $filters['assigned_to'] = $ids;
            } else {
                unset($filters['assigned_to']);
            }
        }

        if (!empty($filters['assigned_by'])) {
            $query->where('assigned_by', $filters['assigned_by']);
        }

        $tasks = $query->paginate(15)->withQueryString();

        // Provide employees list and active filters back to the view (useful for back link and filters UI)
        $employees = Employee::where('company_id', $company->id)->orderBy('name')->get();

        return view('company.tasks.index', compact('tasks', 'company', 'employees', 'filters'));
    }

    public function create(Request $request)
    {
        $company = Auth::user()->company;
        if (!Auth::user()->hasRole(['admin', 'company_admin', 'employee'])) {
            abort(403);
        }

        $employees = Employee::where('company_id' , $company->id)->orderBy('name')->get();
        $designations = \App\Models\Designation::where('company_id', $company->id)->orderBy('title')->get();
        $departments = \App\Models\Department::where('company_id', $company->id)->orderBy('name')->get();

        return view('company.tasks.create', compact('company', 'employees', 'designations', 'departments'));
    }

    public function store(Request $request)
    {
        $company = Auth::user()->company;
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:employees,id',
            'designations' => 'nullable|array',
            'designations.*' => 'exists:designations,id',
            'departments' => 'nullable|array',
            'departments.*' => 'exists:departments,id',
            'exemptions' => 'nullable|array',
            'exemptions.*' => 'exists:employees,id',
            'team_lead_id' => 'nullable|exists:employees,id',
            'priority' => 'required|in:low,medium,high,critical',
            'due_at' => 'nullable|date',
        ]);

        $task = Task::create(array_merge(collect($data)->except(['assigned_to', 'exemptions'])->toArray(), [
            'company_id' => $company->id,
            'assigned_by' => Auth::id(),
            'status' => 'open',
        ]));

        // sync designations
        if (!empty($data['designations'])) {
            $task->designations()->sync($data['designations']);
        }

        // sync departments
        if (!empty($data['departments'])) {
            $task->departments()->sync($data['departments']);
        }

        // sync exemptions
        if (!empty($data['exemptions'])) {
            $task->exemptions()->sync($data['exemptions']);
        }

        // compute final assignee ids from assigned_to, designations and departments
        $assigneeIds = collect($data['assigned_to'] ?? [])->unique();
        if (!empty($data['designations'])) {
            $assigneeIds = $assigneeIds->merge(Employee::whereIn('designation_id', $data['designations'])->pluck('id'));
        }
        if (!empty($data['departments'])) {
            $assigneeIds = $assigneeIds->merge(Employee::whereIn('department_id', $data['departments'])->pluck('id'));
        }

        // Remove exempted employees
        if (!empty($data['exemptions'])) {
            $assigneeIds = $assigneeIds->diff($data['exemptions']);
        }

        $assigneeIds = $assigneeIds->filter()->unique()->values()->all();

        // persist pivot
        $task->assignedToMany()->sync($assigneeIds);

        // keep backward-compatible assigned_to field as first assignee if any
        $task->assigned_to = $assigneeIds[0] ?? null;
        $task->save();

        return redirect()->route('tasks.index' , ['assigned_to' => Auth::user()->employee->id ?? null])->with('success', 'Task created successfully');
    }

    public function show($id)
    {
        $company = Auth::user()->company;
        $task = Task::forCompany($company->id)->with(['assignedToMany', 'assignedBy'])->findOrFail($id);

        return view('company.tasks.show', compact('task', 'company'));
    }

    public function edit(Request $request, $id)
    {
        $company = Auth::user()->company;
        $task = Task::forCompany($company->id)->findOrFail($id);
        $employees = Employee::where('company_id', $company->id)->orderBy('name')->get();
        $designations = \App\Models\Designation::where('company_id', $company->id)->orderBy('title')->get();
        $departments = \App\Models\Department::where('company_id', $company->id)->orderBy('name')->get();

        return view('company.tasks.edit', compact('task', 'employees', 'company', 'designations', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $company = Auth::user()->company;
        $task = Task::forCompany($company->id)->findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:employees,id',
            'designations' => 'nullable|array',
            'designations.*' => 'exists:designations,id',
            'departments' => 'nullable|array',
            'departments.*' => 'exists:departments,id',
            'exemptions' => 'nullable|array',
            'exemptions.*' => 'exists:employees,id',
            'team_lead_id' => 'nullable|exists:employees,id',
            'priority' => 'required|in:low,medium,high,critical',
            'due_at' => 'nullable|date',
            'status' => 'required|in:open,in_progress,completed,closed',
        ]);

        if ($data['status'] === 'completed' && !$task->completed_at) {
            $data['completed_at'] = Carbon::now();
        }


        $task->update(collect($data)->except(['assigned_to', 'exemptions'])->toArray());

        // sync designations
        if (array_key_exists('designations', $data)) {
            $task->designations()->sync($data['designations'] ?? []);
        }

        // sync departments
        if (array_key_exists('departments', $data)) {
            $task->departments()->sync($data['departments'] ?? []);
        }

        // sync exemptions
        if (array_key_exists('exemptions', $data)) {
            $task->exemptions()->sync($data['exemptions'] ?? []);
        }

        if (array_key_exists('assigned_to', $data)) {
            // compute merged set similar to store
            $assigneeIds = collect($data['assigned_to'] ?? [])->unique();
            if (!empty($data['designations'])) {
                $assigneeIds = $assigneeIds->merge(Employee::whereIn('designation_id', $data['designations'])->pluck('id'));
            }
            if (!empty($data['departments'])) {
                $assigneeIds = $assigneeIds->merge(Employee::whereIn('department_id', $data['departments'])->pluck('id'));
            }

            // Remove exempted employees
            if (!empty($data['exemptions'])) {
                $assigneeIds = $assigneeIds->diff($data['exemptions']);
            }

            $assigneeIds = $assigneeIds->filter()->unique()->values()->all();

            $task->assignedToMany()->sync($assigneeIds);

            $task->assigned_to = $assigneeIds[0] ?? null;
            $task->save();
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully');
    }

  
public function updateStatus(Request $request, Task $task)
{
    $user = Auth::user();
    $employee = $user->employee;
    
    // Permission check - must be task creator, admin, team lead, OR assigned to task
    $isTaskCreator = $task->assigned_by == $user->id;
    $isAdmin = $user->hasRole(['admin', 'company_admin']);
    $isTeamLead = $task->team_lead_id && $employee && $task->team_lead_id == $employee->id;
    $isAssignedToTask = $employee && $task->assignedToMany->contains($employee);
    
    if (!($isTaskCreator || $isAdmin || $isTeamLead || $isAssignedToTask)) {
        return response()->json(['error' => 'You do not have permission to update this task status'], 403);
    }
    
    $status = $request->input('status');
    
    // Validate status
    $validStatuses = ['open', 'in_progress', 'completed', 'closed'];
    if (!in_array($status, $validStatuses)) {
        return response()->json(['error' => 'Invalid status'], 400);
    }
    
    $task->update(['status' => $status]);
    
    return response()->json([
        'success' => true,
        'message' => 'Task status updated successfully'
    ]);
}

    /**
     * Request due date extension
     */
    public function requestExtension(Request $request, $id)
    {
        $company = Auth::user()->company;
        $task = Task::forCompany($company->id)->findOrFail($id);

        // Check if user is assigned to task or is team lead
        $currentUser = Auth::user();
        $currentEmployee = $currentUser->employee ?? null;
        $isAssignedToTask = $currentEmployee && $task->assignedToMany->contains($currentEmployee);
        $isTeamLead = $currentEmployee && $task->team_lead_id && $currentEmployee->id == $task->team_lead_id;

        if (!$isAssignedToTask && !$isTeamLead) {
            return response()->json(['error' => 'You are not authorized to request extension'], 403);
        }

        $request->validate([
            'requested_due_date' => 'required|date|after:today',
            'reason' => 'required|string|min:10|max:500',
        ]);

        // Check if there's already a pending extension request
        $pendingRequest = $task->extensionRequests()->where('status', 'pending')->first();
        if ($pendingRequest) {
            return response()->json(['error' => 'A pending extension request already exists for this task'], 400);
        }

        $extensionRequest = TaskExtensionRequest::create([
            'task_id' => $task->id,
            'requested_by' => $currentEmployee->id,
            'current_due_date' => $task->due_at,
            'requested_due_date' => $request->requested_due_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Extension request submitted successfully',
            'request' => $extensionRequest
        ]);
    }

    /**
     * Approve or reject extension request
     */
    public function approveExtension(Request $request, $taskId, $requestId)
    {
        $company = Auth::user()->company;
        $task = Task::forCompany($company->id)->findOrFail($taskId);

        // Only task creator or admin can approve
        $currentUser = Auth::user();
        $isTaskCreator = $currentUser->id == $task->assigned_by;
        $isAdmin = $currentUser->hasRole(['admin', 'company_admin']);

        if (!$isTaskCreator && !$isAdmin) {
            return response()->json(['error' => 'Only task creator or admin can approve extensions'], 403);
        }

        $extensionRequest = TaskExtensionRequest::where('task_id', $task->id)->findOrFail($requestId);

        if ($extensionRequest->status !== 'pending') {
            return response()->json(['error' => 'This request has already been processed'], 400);
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'comment' => 'nullable|string|max:500',
        ]);

        $currentEmployee = $currentUser->employee;
        $extensionRequest->update([
            'status' => $request->action === 'approve' ? 'approved' : 'rejected',
            'approved_by' => $currentEmployee?->id,
            'approval_comment' => $request->comment,
        ]);

        // Update task due date if approved
        if ($request->action === 'approve') {
            $task->update(['due_at' => $extensionRequest->requested_due_date]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Extension request ' . ($request->action === 'approve' ? 'approved' : 'rejected'),
            'request' => $extensionRequest
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $company = Auth::user()->company;
        $task = Task::forCompany($company->id)->findOrFail($id);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted');
    }
}
