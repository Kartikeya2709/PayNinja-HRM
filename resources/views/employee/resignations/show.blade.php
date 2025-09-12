@extends('layouts.app')

@section('title', 'Resignation Details')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Resignation Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('employee.resignations.index') }}">Resignations</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Resignation Information</h4>
                        <div class="card-header-action">
                            <a href="{{ route('employee.resignations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Resignation Type</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge badge-info">{{ $resignation->resignation_type_label }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge badge-{{ $resignation->status_color }}">
                                            {{ $resignation->status_label }}
                                        </span>
                                    </p>
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
                                            <strong>{{ ceil($resignation->remaining_days) }} days remaining</strong> until your last working date.
                                        @else
                                            Your last working date has passed. Your employment has ended.
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
                                <label class="form-label">Your Remarks</label>
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

                        <!-- Approval Information -->
                        @if($resignation->reportingManager)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Reporting Manager</label>
                                        <p class="form-control-plaintext">{{ $resignation->reportingManager->name }}</p>
                                    </div>
                                </div>
                                @if($resignation->hrAdmin)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">HR Admin</label>
                                            <p class="form-control-plaintext">{{ $resignation->hrAdmin->name }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Approval Remarks -->
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

                        <!-- Exit Process Status -->
                        @if($resignation->status === 'approved')
                            <div class="mt-4">
                                <h5>Exit Process Status</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Exit Interview</label>
                                            <p class="form-control-plaintext">
                                                @if($resignation->exit_interview_completed)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Completed
                                                    </span>
                                                    @if($resignation->exit_interview_date)
                                                        <br><small>On {{ $resignation->exit_interview_date->format('M d, Y') }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Handover</label>
                                            <p class="form-control-plaintext">
                                                @if($resignation->handover_completed)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Completed
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Assets Returned</label>
                                            <p class="form-control-plaintext">
                                                @if($resignation->assets_returned)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Completed
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Final Settlement</label>
                                            <p class="form-control-plaintext">
                                                @if($resignation->final_settlement_completed)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Completed
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if($resignation->isExitProcessComplete())
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i>
                                        <strong>All exit processes have been completed!</strong>
                                        Your resignation process is now complete.
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="form-group mt-4">
                            @if($resignation->canBeWithdrawn())
                                <button type="button" class="btn btn-danger"
                                        onclick="withdrawResignation({{ $resignation->id }})">
                                    <i class="fas fa-times"></i> Withdraw Resignation
                                </button>
                            @endif

                            @if($resignation->status === 'pending')
                                <a href="{{ route('employee.resignations.edit', $resignation) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Request
                                </a>
                            @endif
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
function withdrawResignation(resignationId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to withdraw this resignation request? This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, withdraw it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/employee/resignations/${resignationId}/withdraw`;

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush