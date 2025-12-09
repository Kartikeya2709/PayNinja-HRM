@extends('layouts.app')

@section('title', 'Task Details')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('tasks.index', ['assigned_to' => Auth::user()->employee->id ?? null]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Tasks</a>
                <div>
                    @php
                        $currentUserId = Auth::id();
                        $taskCreatorId = $task->assigned_by;
                        $isTaskCreator = $currentUserId == $taskCreatorId;
                        $currentUserEmployee = Auth::user()->employee ?? null;
                        $isAssignedToTask = $currentUserEmployee && $task->assignedToMany->contains($currentUserEmployee);
                        $isTeamLead = $task->team_lead_id && $currentUserEmployee && $task->team_lead_id == $currentUserEmployee->id;
                        $user = Auth::user();
                        $isAdmin = $user->hasRole(['admin', 'company_admin']);
                        $canUpdateStatus = $isTaskCreator || $isAdmin || $isTeamLead || $isAssignedToTask;
                        // dd($canUpdateStatus);
                        // dd($currentUserId, $taskCreatorId, $isTaskCreator, $currentUserEmployee, $isAssignedToTask, $isTeamLead, $isAdmin, $canUpdateStatus);
                    @endphp
                    
                    @if($isTaskCreator || $isAdmin) 
                        <!-- Full CRUD for task creator or admin --> 
                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary me-2"><i class="fas fa-edit"></i> Edit Task</a>
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this task?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete Task</button>
                        </form>
                    @elseif($canUpdateStatus)
                        <!-- Status update for task creator, admin, or team lead -->
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
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
                        
                        <!-- Extension request button for assigned/team lead -->
                        @php
                            $showExtensionBtn = $task->due_at && (
                                Carbon\Carbon::now()->gt($task->due_at) || 
                                (Carbon\Carbon::now()->diffInDays($task->due_at) <= 3 && Carbon\Carbon::now()->diffInDays($task->due_at) >= 0)
                            ) && $task->status !== 'completed';
                        @endphp
                        @if($showExtensionBtn)
                            <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#extensionModal">
                                <i class="fas fa-hourglass-end"></i> Request Extension
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-task"></i> {{ $task->title }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong class="text-muted"><i class="fas fa-info-circle"></i> Status:</strong>
                                @php
                                    $statusBadge = match($task->status) {
                                        'open' => 'badge bg-warning text-dark',
                                        'in_progress' => 'badge bg-info',
                                        'completed' => 'badge bg-success',
                                        'closed' => 'badge bg-secondary',
                                        default => 'badge bg-light text-dark'
                                    };
                                @endphp
                                <span class="{{ $statusBadge }} ms-2 status-badge">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            </div>
                            <div class="mb-3">
                                <strong class="text-muted"><i class="fas fa-exclamation-triangle"></i> Priority:</strong>
                                @php
                                    $priorityBadge = match($task->priority) {
                                        'low' => 'badge bg-light text-dark',
                                        'medium' => 'badge bg-primary',
                                        'high' => 'badge bg-warning text-dark',
                                        'critical' => 'badge bg-danger',
                                        default => 'badge bg-light text-dark'
                                    };
                                @endphp
                                <span class="{{ $priorityBadge }} ms-2">{{ ucfirst($task->priority) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong class="text-muted"><i class="fas fa-users"></i> Assigned To:</strong>
                                <span class="ms-2">{{ $task->assignedToMany->pluck('name')->join(', ') ?: 'Unassigned' }}</span>
                            </div>
                            @if($task->designations && $task->designations->count())
                                <div class="mb-3">
                                    <strong class="text-muted"><i class="fas fa-id-badge"></i> Designations:</strong>
                                    <span class="ms-2">{{ $task->designations->pluck('title')->join(', ') }}</span>
                                </div>
                            @endif
                            @if($task->departments && $task->departments->count())
                                <div class="mb-3">
                                    <strong class="text-muted"><i class="fas fa-building"></i> Departments:</strong>
                                    <span class="ms-2">{{ $task->departments->pluck('name')->join(', ') }}</span>
                                </div>
                            @endif
                            @if($task->teamLead)
                                <div class="mb-3">
                                    <strong class="text-muted"><i class="fas fa-user-tie"></i> Team Lead:</strong>
                                    <span class="ms-2 badge bg-info">{{ $task->teamLead->name }}</span>
                                </div>
                            @endif
                            @if($task->exemptions && $task->exemptions->count())
                                <div class="mb-3">
                                    <strong class="text-muted"><i class="fas fa-ban"></i> Exempt Employees:</strong>
                                    <span class="ms-2 text-danger">{{ $task->exemptions->pluck('name')->join(', ') }}</span>
                                </div>
                            @endif
                            <div class="mb-3">
                                <strong class="text-muted"><i class="fas fa-calendar"></i> Due Date:</strong>
                                <span class="ms-2">{{ $task->due_at ? $task->due_at->format('F j, Y') : 'No due date' }}</span>
                                @if($task->isOverdue())
                                    <span class="ms-2 badge bg-danger"><i class="fas fa-exclamation-circle"></i> OVERDUE</span>
                                @elseif($task->daysUntilDue() !== null && $task->daysUntilDue() <= 3 && $task->daysUntilDue() > 0)
                                    <span class="ms-2 badge bg-warning text-dark"><i class="fas fa-clock"></i> Due in {{ $task->daysUntilDue() }} days</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <hr>
                    @php
                        $pendingExtensionRequest = $task->pendingExtensionRequest;
                    @endphp
                    
                    <!-- Pending Extension Request Section (for task creator/admin) -->
                    @if($pendingExtensionRequest && ($isTaskCreator || $isAdmin))
                        <div class="mt-4 mb-4">
                            <h5 class="text-muted mb-3"><i class="fas fa-hourglass-half"></i> Pending Extension Request</h5>
                            <div class="card border-warning">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Requested By:</strong> {{ $pendingExtensionRequest->requestedBy->name }}</p>
                                            <p><strong>Current Due Date:</strong> {{ $pendingExtensionRequest->current_due_date->format('F j, Y') }}</p>
                                            <p><strong>Requested Due Date:</strong> {{ $pendingExtensionRequest->requested_due_date->format('F j, Y') }}</p>
                                        </div>
                                        <div class="col-md-8">
                                            <p><strong>Reason:</strong></p>
                                            <p class="bg-light p-2 rounded">{{ $pendingExtensionRequest->reason }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <form id="extensionApprovalForm" class="row g-2">
                                            @csrf
                                            <div class="col-md-8">
                                                <textarea class="form-control" id="approvalComment" name="comment" placeholder="Add approval/rejection comment (optional)" rows="2" maxlength="500"></textarea>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-success w-100 approve-extension" data-task-id="{{ $task->id }}" data-request-id="{{ $pendingExtensionRequest->id }}">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger w-100 reject-extension" data-task-id="{{ $task->id }}" data-request-id="{{ $pendingExtensionRequest->id }}">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    @endif
                    
                    <div class="mt-4">
                        <h5 class="text-muted mb-3"><i class="fas fa-align-left"></i> Description</h5>
                        <div class="bg-light p-3 rounded">{!! nl2br(e($task->description)) !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Helper function to show alerts (GLOBAL - accessible by all scripts)
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
    $('.container').prepend(alertHtml);
    
    // Auto-dismiss success alerts after 3 seconds
    if (type === 'success') {
        setTimeout(() => {
            $('.alert-success').fadeOut();
        }, 3000);
    }
}

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
                const statusBadge = $('.status-badge');
                
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
                
                statusBadge.removeClass().addClass(newBadgeClass + ' ms-2 status-badge');
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
});
</script>

<!-- Extension Request Modal -->
<div class="modal fade" id="extensionModal" tabindex="-1" aria-labelledby="extensionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="extensionModalLabel"><i class="fas fa-hourglass-end"></i> Request Due Date Extension</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="extensionRequestForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="currentDueDate" class="form-label"><strong>Current Due Date</strong></label>
                        <input type="text" class="form-control" id="currentDueDate" value="{{ $task->due_at?->format('F j, Y') }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="requestedDueDate" class="form-label"><strong>Requested New Due Date</strong></label>
                        <input type="date" class="form-control" id="requestedDueDate" name="requested_due_date" required>
                        <small class="text-muted">Must be in the future</small>
                    </div>
                    <div class="mb-3">
                        <label for="extensionReason" class="form-label"><strong>Reason for Extension</strong></label>
                        <textarea class="form-control" id="extensionReason" name="reason" rows="4" required placeholder="Please explain why you need more time to complete this task..." maxlength="500"></textarea>
                        <small class="text-muted d-block mt-1"><span id="charCount">0</span>/500 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="submitExtensionBtn">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Extension request form character counter
$('#extensionReason').on('input', function() {
    $('#charCount').text($(this).val().length);
});

// Submit extension request
$('#submitExtensionBtn').on('click', function() {
    const taskId = {{ $task->id }};
    const requestedDueDate = $('#requestedDueDate').val();
    const reason = $('#extensionReason').val();

    if (!requestedDueDate || !reason) {
        showAlert('danger', 'Please fill in all fields');
        return;
    }

    const btn = $(this);
    btn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...').prop('disabled', true);

    $.ajax({
        url: `/tasks/${taskId}/request-extension`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            requested_due_date: requestedDueDate,
            reason: reason
        },
        success: function(response) {
            showAlert('success', response.message);
            $('#extensionModal').modal('hide');
            $('#extensionRequestForm')[0].reset();
            setTimeout(() => {
                location.reload();
            }, 2000);
        },
        error: function(xhr) {
            let errorMessage = 'Failed to submit extension request';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            showAlert('danger', errorMessage);
        },
        complete: function() {
            btn.html('<i class="fas fa-paper-plane"></i> Submit Request').prop('disabled', false);
        }
    });
});

// Approve extension
$(document).on('click', '.approve-extension', function() {
    approveOrRejectExtension($(this), 'approve');
});

// Reject extension
$(document).on('click', '.reject-extension', function() {
    approveOrRejectExtension($(this), 'reject');
});

function approveOrRejectExtension(btn, action) {
    const taskId = btn.data('task-id');
    const requestId = btn.data('request-id');
    const comment = $('#approvalComment').val();

    if (!confirm(`Are you sure you want to ${action} this extension request?`)) {
        return;
    }

    btn.html(`<i class="fas fa-spinner fa-spin"></i> ${action === 'approve' ? 'Approving' : 'Rejecting'}...`).prop('disabled', true);

    $.ajax({
        url: `/tasks/${taskId}/extension-request/${requestId}/approve`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            action: action,
            comment: comment
        },
        success: function(response) {
            showAlert('success', response.message);
            setTimeout(() => {
                location.reload();
            }, 2000);
        },
        error: function(xhr) {
            let errorMessage = 'Failed to process extension request';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            showAlert('danger', errorMessage);
        },
        complete: function() {
            btn.prop('disabled', false).html(`<i class="fas fa-${action === 'approve' ? 'check' : 'times'}"></i> ${action === 'approve' ? 'Approve' : 'Reject'}`);
        }
    });
}
</script>
@endsection
