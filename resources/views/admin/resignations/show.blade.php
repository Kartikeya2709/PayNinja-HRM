@extends('layouts.app')

@section('title', 'Resignation Details - Admin')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Resignation Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.resignations.index') }}">Resignations</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Employee Information</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.resignations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                @if($resignation->employee->profile_image)
                                    <img src="{{ asset('storage/' . $resignation->employee->profile_image) }}"
                                         class="img-fluid rounded" alt="Profile Image">
                                @else
                                    <div class="text-center">
                                        <div class="avatar avatar-xl mb-3">
                                            <span class="avatar-title rounded-circle bg-primary" style="width: 80px; height: 80px; font-size: 32px;">
                                                {{ substr($resignation->employee->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Employee Name</label>
                                            <p class="form-control-plaintext font-weight-bold">{{ $resignation->employee->name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Employee Code</label>
                                            <p class="form-control-plaintext">{{ $resignation->employee->employee_code }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Department</label>
                                            <p class="form-control-plaintext">{{ $resignation->employee->department->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Designation</label>
                                            <p class="form-control-plaintext">{{ $resignation->employee->designation->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <p class="form-control-plaintext">{{ $resignation->employee->email }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Phone</label>
                                            <p class="form-control-plaintext">{{ $resignation->employee->phone ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resignation Details -->
                <div class="card">
                    <div class="card-header">
                        <h4>Resignation Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Resignation Type</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge badge-info">{{ $resignation->resignation_type_label }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge badge-{{ $resignation->status_color }}">
                                            {{ $resignation->status_label }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Submitted Date</label>
                                    <p class="form-control-plaintext">{{ $resignation->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Resignation Date</label>
                                    <p class="form-control-plaintext">{{ $resignation->resignation_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Last Working Date</label>
                                    <p class="form-control-plaintext">{{ $resignation->last_working_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Notice Period</label>
                                    <p class="form-control-plaintext">{{ $resignation->notice_period_days }} days</p>
                                </div>
                            </div>
                        </div>

                        @if($resignation->remaining_days !== null)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-{{ $resignation->remaining_days > 0 ? 'info' : 'warning' }}">
                                        <i class="fas fa-clock"></i>
                                        @if($resignation->remaining_days > 0)
                                            <strong>{{ $resignation->remaining_days }} days remaining</strong> until last working date.
                                        @else
                                            Last working date has passed. Employment has ended.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="form-label">Reason for Resignation</label>
                            <p class="form-control-plaintext">{{ $resignation->reason }}</p>
                        </div>

                        @if($resignation->employee_remarks)
                            <div class="form-group">
                                <label class="form-label">Employee Remarks</label>
                                <p class="form-control-plaintext">{{ $resignation->employee_remarks }}</p>
                            </div>
                        @endif

                        @if($resignation->attachment_path)
                            <div class="form-group">
                                <label class="form-label">Supporting Document</label>
                                <p class="form-control-plaintext">
                                    <a href="{{ Storage::url($resignation->attachment_path) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Download Document
                                    </a>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Approval Information -->
                <div class="card">
                    <div class="card-header">
                        <h4>Approval Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($resignation->reportingManager)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Reporting Manager</label>
                                        <p class="form-control-plaintext">{{ $resignation->reportingManager->name }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($resignation->hrAdmin)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">HR Admin</label>
                                        <p class="form-control-plaintext">{{ $resignation->hrAdmin->name }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($resignation->approver)
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Final Approver</label>
                                        <p class="form-control-plaintext">{{ $resignation->approver->name }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($resignation->manager_remarks)
                            <div class="form-group">
                                <label class="form-label">Manager Remarks</label>
                                <p class="form-control-plaintext">{{ $resignation->manager_remarks }}</p>
                            </div>
                        @endif

                        @if($resignation->hr_remarks)
                            <div class="form-group">
                                <label class="form-label">HR Remarks</label>
                                <p class="form-control-plaintext">{{ $resignation->hr_remarks }}</p>
                            </div>
                        @endif

                        @if($resignation->admin_remarks)
                            <div class="form-group">
                                <label class="form-label">Admin Remarks</label>
                                <p class="form-control-plaintext">{{ $resignation->admin_remarks }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Exit Process Management -->
                @if($resignation->status === 'approved')
                    <div class="card">
                        <div class="card-header">
                            <h4>Exit Process Management</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Exit Interview -->
                                <div class="col-md-3">
                                    <div class="card border-{{ $resignation->exit_interview_completed ? 'success' : 'warning' }}">
                                        <div class="card-body text-center">
                                            <i class="fas fa-user-tie fa-2x mb-2 text-{{ $resignation->exit_interview_completed ? 'success' : 'warning' }}"></i>
                                            <h6>Exit Interview</h6>
                                            @if($resignation->exit_interview_completed)
                                                <span class="badge badge-success">Completed</span>
                                                @if($resignation->exit_interview_date)
                                                    <br><small>On {{ $resignation->exit_interview_date->format('M d, Y') }}</small>
                                                @endif
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                                <br>
                                                <button type="button" class="btn btn-sm btn-success mt-2"
                                                        onclick="completeExitInterview({{ $resignation->id }})">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Handover -->
                                <div class="col-md-3">
                                    <div class="card border-{{ $resignation->handover_completed ? 'success' : 'warning' }}">
                                        <div class="card-body text-center">
                                            <i class="fas fa-handshake fa-2x mb-2 text-{{ $resignation->handover_completed ? 'success' : 'warning' }}"></i>
                                            <h6>Handover</h6>
                                            @if($resignation->handover_completed)
                                                <span class="badge badge-success">Completed</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                                <br>
                                                <button type="button" class="btn btn-sm btn-success mt-2"
                                                        onclick="completeHandover({{ $resignation->id }})">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Assets Return -->
                                <div class="col-md-3">
                                    <div class="card border-{{ $resignation->assets_returned ? 'success' : 'warning' }}">
                                        <div class="card-body text-center">
                                            <i class="fas fa-box fa-2x mb-2 text-{{ $resignation->assets_returned ? 'success' : 'warning' }}"></i>
                                            <h6>Assets Return</h6>
                                            @if($resignation->assets_returned)
                                                <span class="badge badge-success">Completed</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                                <br>
                                                <button type="button" class="btn btn-sm btn-success mt-2"
                                                        onclick="markAssetsReturned({{ $resignation->id }})">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Final Settlement -->
                                <div class="col-md-3">
                                    <div class="card border-{{ $resignation->final_settlement_completed ? 'success' : 'warning' }}">
                                        <div class="card-body text-center">
                                            <i class="fas fa-money-bill-wave fa-2x mb-2 text-{{ $resignation->final_settlement_completed ? 'success' : 'warning' }}"></i>
                                            <h6>Final Settlement</h6>
                                            @if($resignation->final_settlement_completed)
                                                <span class="badge badge-success">Completed</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                                <br>
                                                <button type="button" class="btn btn-sm btn-success mt-2"
                                                        onclick="completeFinalSettlement({{ $resignation->id }})">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($resignation->isExitProcessComplete())
                                <div class="alert alert-success mt-3">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>All exit processes have been completed!</strong>
                                    The resignation process is now complete.
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                @if(in_array($resignation->status, ['pending', 'hr_approved', 'manager_approved']))
                                    <div class="btn-group" role="group" aria-label="Approval Actions">
                                        <button type="button" class="btn btn-success"
                                                onclick="approveResignation({{ $resignation->id }})">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-danger"
                                                onclick="rejectResignation({{ $resignation->id }})">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Approval functions
function approveResignation(resignationId) {
    Swal.fire({
        title: 'Approve Resignation',
        html: `
            <div class="form-group">
                <label for="exit_interview_date">Exit Interview Date (Optional)</label>
                <input type="date" id="exit_interview_date" class="form-control"
                       min="${new Date().toISOString().split('T')[0]}"
                       max="${new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}">
            </div>
            <div class="form-group">
                <label for="approval_remarks">Approval Remarks (Optional)</label>
                <textarea id="approval_remarks" class="form-control" rows="3" placeholder="Enter approval remarks..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Approve',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            const exitInterviewDate = document.getElementById('exit_interview_date').value;
            const remarks = document.getElementById('approval_remarks').value;

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/approve`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            if (exitInterviewDate) {
                const dateInput = document.createElement('input');
                dateInput.type = 'hidden';
                dateInput.name = 'exit_interview_date';
                dateInput.value = exitInterviewDate;
                form.appendChild(dateInput);
            }

            if (remarks) {
                const remarksInput = document.createElement('input');
                remarksInput.type = 'hidden';
                remarksInput.name = 'remarks';
                remarksInput.value = remarks;
                form.appendChild(remarksInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function rejectResignation(resignationId) {
    Swal.fire({
        title: 'Reject Resignation',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Enter the reason for rejection...',
        inputValidator: (value) => {
            if (!value) {
                return 'Rejection reason is required!';
            }
        },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        preConfirm: (reason) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/reject`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Exit process functions
function completeExitInterview(resignationId) {
    Swal.fire({
        title: 'Complete Exit Interview',
        html: `
            <div class="form-group">
                <label for="exit_interview_date">Interview Date</label>
                <input type="date" id="exit_interview_date" class="form-control"
                       value="${new Date().toISOString().split('T')[0]}" required>
            </div>
            <div class="form-group">
                <label for="exit_interview_remarks">Interview Remarks (Optional)</label>
                <textarea id="exit_interview_remarks" class="form-control" rows="3" placeholder="Enter interview remarks..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Complete Interview',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            const date = document.getElementById('exit_interview_date').value;
            const remarks = document.getElementById('exit_interview_remarks').value;

            if (!date) {
                Swal.showValidationMessage('Interview date is required');
                return false;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/complete-exit-interview`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const dateInput = document.createElement('input');
            dateInput.type = 'hidden';
            dateInput.name = 'exit_interview_date';
            dateInput.value = date;
            form.appendChild(dateInput);

            if (remarks) {
                const remarksInput = document.createElement('input');
                remarksInput.type = 'hidden';
                remarksInput.name = 'exit_interview_remarks';
                remarksInput.value = remarks;
                form.appendChild(remarksInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function completeHandover(resignationId) {
    Swal.fire({
        title: 'Complete Handover Process',
        html: `
            <div class="form-group">
                <label for="handover_remarks">Handover Remarks (Optional)</label>
                <textarea id="handover_remarks" class="form-control" rows="3" placeholder="Enter handover details..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Complete Handover',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: (remarks) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/complete-handover`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            if (remarks) {
                const remarksInput = document.createElement('input');
                remarksInput.type = 'hidden';
                remarksInput.name = 'handover_remarks';
                remarksInput.value = remarks;
                form.appendChild(remarksInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function markAssetsReturned(resignationId) {
    Swal.fire({
        title: 'Mark Assets as Returned',
        input: 'textarea',
        inputLabel: 'Assets Return Remarks (Optional)',
        inputPlaceholder: 'Enter details about returned assets...',
        showCancelButton: true,
        confirmButtonText: 'Mark as Returned',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: (remarks) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/mark-assets-returned`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            if (remarks) {
                const remarksInput = document.createElement('input');
                remarksInput.type = 'hidden';
                remarksInput.name = 'assets_remarks';
                remarksInput.value = remarks;
                form.appendChild(remarksInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function completeFinalSettlement(resignationId) {
    Swal.fire({
        title: 'Complete Final Settlement',
        html: `
            <div class="form-group">
                <label for="settlement_amount">Settlement Amount (₹)</label>
                <input type="number" id="settlement_amount" class="form-control" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="form-group">
                <label for="settlement_remarks">Settlement Remarks (Optional)</label>
                <textarea id="settlement_remarks" class="form-control" rows="3" placeholder="Enter settlement details..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Complete Settlement',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            const amount = document.getElementById('settlement_amount').value;
            const remarks = document.getElementById('settlement_remarks').value;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/complete-final-settlement`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            if (amount) {
                const amountInput = document.createElement('input');
                amountInput.type = 'hidden';
                amountInput.name = 'settlement_amount';
                amountInput.value = amount;
                form.appendChild(amountInput);
            }

            if (remarks) {
                const remarksInput = document.createElement('input');
                remarksInput.type = 'hidden';
                remarksInput.name = 'settlement_remarks';
                remarksInput.value = remarks;
                form.appendChild(remarksInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush