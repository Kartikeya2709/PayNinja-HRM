@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary"><i class="fas fa-tasks"></i> Tasks for {{ $company->name ?? 'Company' }}</h3>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-lg shadow">
            <i class="fas fa-plus"></i> New Task
        </a>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card p-3 mb-3 shadow-sm border-0 bg-light">
                <h6 class="card-title mb-3"><i class="fas fa-filter"></i> Filters</h6>
                <form method="GET" action="{{ route('tasks.index') }}">
                    @php
                        $selectedStatus = $filters['status'] ?? request('status');
                        $selectedPriority = $filters['priority'] ?? request('priority');
                        $selectedAssigned = $filters['assigned_to'] ?? (request('assigned_to') ?: []);
                        if (!is_array($selectedAssigned) && $selectedAssigned) {
                            $selectedAssigned = is_string($selectedAssigned) ? explode(',', $selectedAssigned) : [$selectedAssigned];
                        }
                    @endphp

                    <div class="mb-3">
                        <label class="form-label">Filter by status</label>
                        <select name="status" class="form-select">
                            <option value="">Any</option>
                            <option value="open" {{ ($selectedStatus=='open') ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ ($selectedStatus=='in_progress') ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ ($selectedStatus=='completed') ? 'selected' : '' }}>Completed</option>
                            <option value="closed" {{ ($selectedStatus=='closed') ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="">Any</option>
                            <option value="low" {{ ($selectedPriority=='low') ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ ($selectedPriority=='medium') ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ ($selectedPriority=='high') ? 'selected' : '' }}>High</option>
                            <option value="critical" {{ ($selectedPriority=='critical') ? 'selected' : '' }}>Critical</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to[]" class="form-select" multiple>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ in_array($emp->id, (array) $selectedAssigned) ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple</small>
                    </div>
                    <button class="btn btn-outline-primary w-100"><i class="fas fa-search"></i> Apply</button>
                </form>
            </div>
        </div>

        <div class="col-md-9">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                @forelse($tasks as $task)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 task-card" style="transition: transform 0.2s;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0 text-truncate">{{ $task->title }}</h5>
                                    @php
                                        $statusBadge = match($task->status) {
                                            'open' => 'badge bg-warning text-dark',
                                            'in_progress' => 'badge bg-info',
                                            'completed' => 'badge bg-success',
                                            'closed' => 'badge bg-secondary',
                                            default => 'badge bg-light text-dark'
                                        };
                                        $priorityBadge = match($task->priority) {
                                            'low' => 'badge bg-light text-dark',
                                            'medium' => 'badge bg-primary',
                                            'high' => 'badge bg-warning text-dark',
                                            'critical' => 'badge bg-danger',
                                            default => 'badge bg-light text-dark'
                                        };
                                    @endphp
                                    <span class="{{ $statusBadge }} status-badge">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                </div>
                                <p class="mb-2"><span class="{{ $priorityBadge }}"><i class="fas fa-exclamation-triangle"></i> {{ ucfirst($task->priority) }}</span></p>
                                <p class="mb-2 text-muted small"><i class="fas fa-user"></i> <strong>Assigned:</strong> {{ $task->assignedToMany->pluck('name')->join(', ') ?: 'Unassigned' }}</p>
                                <p class="mb-3 text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($task->description), 120) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i> View</a>
                                        
                                        @php
                                            $currentUserId = Auth::id();
                                            $taskCreatorId = $task->assigned_by;
                                            $isTaskCreator = $currentUserId == $taskCreatorId;
                                            $currentUserEmployee = Auth::user()->employee ?? null;
                                            $isAssignedToTask = $currentUserEmployee && $task->assignedToMany->contains($currentUserEmployee);
                                            $user = Auth::user();
                                            $isAdmin = $user->hasRole(['admin', 'company_admin']);
                                        @endphp
                                        
                                        @if($isTaskCreator || $isAdmin)
                                            <!-- Full CRUD for task creator or admin -->
                                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="fas fa-edit"></i> Edit</a>
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this task?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        @elseif($isAssignedToTask)
                                            <!-- Status update only for assigned users -->
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-sync-alt"></i> Update Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item update-status" href="#" data-task-id="{{ $task->id }}" data-status="in_progress">
                                                        <i class="fas fa-spinner text-info"></i> In Progress
                                                    </a></li>
                                                    <li><a class="dropdown-item update-status" href="#" data-task-id="{{ $task->id }}" data-status="completed">
                                                        <i class="fas fa-check-circle text-success"></i> Completed
                                                    </a></li>
                                                    <li><a class="dropdown-item update-status" href="#" data-task-id="{{ $task->id }}" data-status="closed">
                                                        <i class="fas fa-times-circle text-secondary"></i> Closed
                                                    </a></li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><i class="fas fa-calendar"></i> Due: {{ $task->due_at ? $task->due_at->format('M d, Y') : '—' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center border-0 shadow-sm">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <h5>No tasks found.</h5>
                            <p>Try adjusting your filters or create a new task.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-4">{{ $tasks->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

<style>
.task-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.btn-group .dropdown-toggle::after {
    margin-left: 0.5rem;
}

.update-status:hover {
    background-color: #f8f9fa;
}

.status-success {
    color: #28a745 !important;
}

.status-info {
    color: #17a2b8 !important;
}

.status-warning {
    color: #ffc107 !important;
}

.status-secondary {
    color: #6c757d !important;
}
</style>

<script>
$(document).ready(function() {
    // Handle status updates
    $('.update-status').on('click', function(e) {
        e.preventDefault();
        
        const taskId = $(this).data('task-id');
        const newStatus = $(this).data('status');
        const statusText = $(this).text().trim();
        const dropdown = $(this).closest('.btn-group');
        
        // Show loading state
        const button = dropdown.find('.dropdown-toggle');
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
        
        // Make AJAX request
        $.ajax({
            url: `/tasks/${taskId}/update-status`,
            type: 'PATCH',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status: newStatus
            },
            success: function(response) {
                // Show success message
                showAlert('success', 'Task status updated successfully to ' + statusText);
                
                // Update the status badge in the card
                const card = $(`[data-task-id="${taskId}"]`).closest('.task-card');
                const statusBadge = card.find('.status-badge');
                
                // Remove old status classes
                statusBadge.removeClass('bg-warning bg-info bg-success bg-secondary bg-light text-dark');
                
                // Add new status class based on the new status
                let newBadgeClass = '';
                let statusDisplay = '';
                
                switch(newStatus) {
                    case 'open':
                        newBadgeClass = 'badge bg-warning text-dark';
                        statusDisplay = 'Open';
                        break;
                    case 'in_progress':
                        newBadgeClass = 'badge bg-info';
                        statusDisplay = 'In Progress';
                        break;
                    case 'completed':
                        newBadgeClass = 'badge bg-success';
                        statusDisplay = 'Completed';
                        break;
                    case 'closed':
                        newBadgeClass = 'badge bg-secondary';
                        statusDisplay = 'Closed';
                        break;
                }
                
                statusBadge.removeClass().addClass(newBadgeClass + ' status-badge');
                statusBadge.text(statusDisplay);
                
                // Close dropdown
                dropdown.find('.dropdown-toggle').dropdown('hide');
            },
            error: function(xhr) {
                let errorMessage = 'Failed to update task status';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showAlert('danger', errorMessage);
            },
            complete: function() {
                // Restore button state
                button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Helper function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top of the content area
        $('.container-fluid').prepend(alertHtml);
        
        // Auto-dismiss success alerts after 3 seconds
        if (type === 'success') {
            setTimeout(() => {
                $('.alert-success').fadeOut();
            }, 3000);
        }
    }
});
</script>
@endsection
