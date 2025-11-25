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
                    <div class="card-1">
                        <h5 class="mb-0">Employee Information</h5>
                        <div class="card-header-action">
                            <a href="{{ route('admin.resignations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                    <div class="card glass-card mt-4">
                       <div class="row align-items-center">
                          <div class="col-md-3 text-center mb-3 mb-md-0">
                          @if($resignation->employee->profile_image)
                          <div class="profile-avatar mx-auto">
                             <img src="{{ asset('storage/' . $resignation->employee->profile_image) }}" alt="Profile Image">
                          </div>
                          @else
                          <div class="profile-avatar mx-auto">
                             <span class="avatar-title">
                             {{ substr($resignation->employee->name, 0, 1) }}
                             </span>
                          </div>
                          @endif
                          </div>

                        <div class="col-md-9 emp-info-text">
                           <h4 class="mb-1">{{ $resignation->employee->name }}</h4>
                           <p class="mb-3">{{ $resignation->employee->designation->title ?? 'N/A' }} | {{ $resignation->employee->department->name ?? 'N/A' }}</p>
                           <div class="divider"></div>

                             <div class="row mt-3">
                                <div class="col-md-6 mb-2">
                                   <p class="info-label mb-1"><i class="fas fa-id-badge me-2"></i>Employee Code</p>
                                   <p class="info-value">{{ $resignation->employee->employee_code }}</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                   <p class="info-label mb-1"><i class="fas fa-envelope me-2"></i>Email</p>
                                   <p class="info-value">{{ $resignation->employee->email }}</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                   <p class="info-label mb-1"><i class="fas fa-phone me-2"></i>Phone</p>
                                   <p class="info-value">{{ $resignation->employee->phone ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                   <p class="info-label mb-1"><i class="fas fa-briefcase me-2"></i>Department</p>
                                   <p class="info-value">{{ $resignation->employee->department->name ?? 'N/A' }}</p>
                                </div>
                             </div>
                        </div>
                        </div>
                    </div>

                <!-- Resignation Details -->

                    <div class="card glass-card mt-4">
                       <div class="d-flex align-items-center bg-primary py-2 px-2 text-white rounded-3 mb-4">
                         <h5 class="mb-0"><i class="fas fa-user-times me-2"></i>Resignation Details</h5>
                       </div>

                    <div class="card-body">
                       <!-- Top Info Row -->
                       <div class="row mb-4">
                          <div class="col-md-4 mb-3 mb-md-0">
                             <div class="info-box">
                                <label class="form-label info-label text-muted"><i class="fas fa-clipboard-list me-2"></i>Type</label>
                                <p><span class="badge bg-info text-white px-3 py-2">{{ $resignation->resignation_type_label }}</span></p>
                             </div>
                          </div>
                          <div class="col-md-4 mb-3 mb-md-0">
                             <div class="info-box">
                                <label class="form-label info-label text-muted"><i class="fas fa-flag me-2"></i>Status</label>
                                <p><span class="badge bg-{{ $resignation->status_color }} text-white px-3 py-2">{{ $resignation->status_label }}</span></p>
                             </div>
                          </div>
                          <div class="col-md-4">
                             <div class="info-box">
                                <label class="form-label info-label text-muted"><i class="fas fa-calendar-alt me-2"></i>Submitted</label>
                                <p class="fw-semibold">{{ $resignation->created_at->format('M d, Y H:i') }}</p>
                             </div>
                         </div>
                       </div>

                       <div class="row mb-4">
                          <div class="col-md-4 mb-3 mb-md-0">
                             <label class="form-label info-label text-muted"><i class="fas fa-calendar-day me-2"></i>Resignation Date</label>
                             <p class="fw-semibold">{{ $resignation->resignation_date->format('M d, Y') }}</p>
                          </div>
                          <div class="col-md-4 mb-3 mb-md-0">
                             <label class="form-label info-label text-muted"><i class="fas fa-hourglass-end me-2"></i>Last Working Date</label>
                             <p class="fw-semibold">{{ $resignation->last_working_date->format('M d, Y') }}</p>
                          </div>
                          <div class="col-md-4">
                             <label class="form-label info-label text-muted"><i class="fas fa-clock me-2"></i>Notice Period</label>
                             <p class="fw-semibold">{{ $resignation->notice_period_days }} days</p>
                          </div>
                       </div>

                       @if($resignation->remaining_days !== null)
                         <div class="alert {{ ceil($resignation->remaining_days) > 0 ? 'alert-info' : 'alert-warning' }} text-center rounded-3 shadow-sm">
                            <i class="fas fa-stopwatch me-2"></i>
                            @if($resignation->remaining_days > 0)
                            <strong>{{ ceil($resignation->remaining_days) }} days remaining</strong> until last working date.
                            @else
                            Last working date has passed. Employment has ended.
                            @endif
                         </div>
                       @endif

                       <div class="mt-4">
                          <label class="form-label text-muted"><i class="fas fa-comment-dots me-2"></i>Reason for Resignation</label>
                          <p class="fw-semibold text-dark">{{ $resignation->reason }}</p>
                       </div>

                       @if($resignation->employee_remarks)
                       <div class="mt-3">
                          <label class="form-label text-muted"><i class="fas fa-user-edit me-2"></i>Employee Remarks</label>
                          <p class="fw-semibold text-dark">{{ $resignation->employee_remarks }}</p>
                       </div>
                       @endif

                       @if($resignation->attachment_path)
                       <div class="mt-3 text-center">
                          <a href="{{ Storage::url($resignation->attachment_path) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                          <i class="fas fa-download me-2"></i>Download Supporting Document
                          </a>
                      </div>
                      @endif
                    </div>
                </div>

                <!-- Approval Information -->

                <div class="card mt-4 shadow-glass border-0">
                   <div class="d-flex align-items-center bg-primary py-2 px-2 text-white rounded-3">
                      <i class="fas fa-user-check me-2"></i>
                      <h5 class="mb-0">Approval Information</h5>
                   </div>

                <div class="card-body">
                <!-- Approvers Row -->
                   <div class="row mb-4">
                   @if($resignation->reportingManager)
                      <div class="col-md-4 mb-3 mb-md-0">
                         <div class="info-box p-3 rounded-3 hover-glow">
                            <i class="fas fa-briefcase text-primary mb-2 fs-4"></i>
                            <label class="form-label text-muted d-block">Reporting Manager</label>
                            <p class="fw-semibold text-dark mb-0">{{ $resignation->reportingManager->name }}</p>
                         </div>
                      </div>
                   @endif

                   @if($resignation->hrAdmin)
                      <div class="col-md-4 mb-3 mb-md-0">
                         <div class="info-box p-3 rounded-3 hover-glow">
                            <i class="fas fa-user-tie text-info mb-2 fs-4"></i>
                            <label class="form-label text-muted d-block">HR Admin</label>
                            <p class="fw-semibold text-dark mb-0">{{ $resignation->hrAdmin->name }}</p>
                         </div>
                      </div>
                   @endif

                   @if($resignation->approver)
                      <div class="col-md-4">
                         <div class="info-box p-3 rounded-3 hover-glow">
                            <i class="fas fa-user-shield text-success mb-2 fs-4"></i>
                            <label class="form-label text-muted d-block">Final Approver</label>
                            <p class="fw-semibold text-dark mb-0">{{ $resignation->approver->name }}</p>
                         </div>
                       </div>
                   @endif
                  </div>

                  <!-- Remarks Section -->
                  <div class="mt-4">
                     @if($resignation->manager_remarks)
                        <div class="remark-box mb-3 p-3 rounded-3 shadow-sm bg-light">
                           <label class="form-label text-primary"><i class="fas fa-comments me-1"></i>Manager Remarks</label>
                           <p class="fw-semibold mb-0">{{ $resignation->manager_remarks }}</p>
                        </div>
                     @endif

                     @if($resignation->hr_remarks)
                        <div class="remark-box mb-3 p-3 rounded-3 shadow-sm bg-light">
                           <label class="form-label text-info"><i class="fas fa-comment-alt me-1"></i>HR Remarks</label>
                           <p class="fw-semibold mb-0">{{ $resignation->hr_remarks }}</p>
                        </div>
                     @endif

                     @if($resignation->admin_remarks)
                        <div class="remark-box p-3 rounded-3 shadow-sm bg-light">
                           <label class="form-label text-success"><i class="fas fa-sticky-note me-1"></i>Admin Remarks</label>
                           <p class="fw-semibold mb-0">{{ $resignation->admin_remarks }}</p>
                        </div>
                     @endif

                     @if($resignation->final_settlement_document_path)
                        <div class="mt-3 text-center">
                           <a href="{{ Storage::url($resignation->final_settlement_document_path) }}" target="_blank" class="btn btn-outline-success rounded-pill px-4 shadow-sm">
                              <i class="fas fa-file-download me-2"></i>Download Final Settlement Document
                           </a>
                        </div>
                     @endif
                  </div>
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
                <div class="">
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
    // First, fetch assigned assets
    fetch(`/admin/resignations/${resignationId}/assigned-assets`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(assets => {
        if (assets.length === 0) {
            // No assets assigned, proceed with simple confirmation
            Swal.fire({
                title: 'Mark Assets as Returned',
                text: 'No assets are currently assigned to this employee. Mark as returned?',
                input: 'textarea',
                inputLabel: 'Remarks (Optional)',
                inputPlaceholder: 'Enter details...',
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
        } else {
            // Show assets selection modal
            let assetsHtml = '<div class="form-group"><label>Select Assets to Return:</label><br>';
            assets.forEach(asset => {
                assetsHtml += `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${asset.id}" id="asset_${asset.id}" name="asset_ids[]">
                        <label class="form-check-label"   for="asset_${asset.id}">
                            <strong>${asset.asset_name}</strong> (${asset.asset_code})
                            ${asset.assigned_date ? `<br><small class="text-muted">Assigned: ${asset.assigned_date}</small>` : ''}
                            ${asset.condition_on_assignment ? `<br><small class="text-muted">Condition: ${asset.condition_on_assignment}</small>` : ''}
                        </label>
                    </div>
                `;
            });
            assetsHtml += '</div><div class="form-group"><label for="assets_remarks">Remarks (Optional)</label><textarea id="assets_remarks" class="form-control" rows="3" placeholder="Enter details about returned assets..."></textarea></div>';

            Swal.fire({
                title: 'Mark Assets as Returned',
                html: assetsHtml,
                showCancelButton: true,
                confirmButtonText: 'Mark Selected as Returned',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                preConfirm: () => {
                    const selectedAssets = Array.from(document.querySelectorAll('input[name="asset_ids[]"]:checked')).map(cb => cb.value);
                    const remarks = document.getElementById('assets_remarks').value;

                    if (selectedAssets.length === 0) {
                        Swal.showValidationMessage('Please select at least one asset to return');
                        return false;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/resignations/${resignationId}/mark-assets-returned`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    selectedAssets.forEach(assetId => {
                        const assetInput = document.createElement('input');
                        assetInput.type = 'hidden';
                        assetInput.name = 'asset_ids[]';
                        assetInput.value = assetId;
                        form.appendChild(assetInput);
                    });

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
    })
    .catch(error => {
        console.error('Error fetching assets:', error);
        Swal.fire('Error', 'Failed to load assigned assets.', 'error');
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
            <div class="form-group">
                <label for="final_settlement_document">Final Payment Document (PDF/Screenshot - Optional)</label>
                <input type="file" id="final_settlement_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <small class="text-muted">Upload PDF or image file (max 5MB)</small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Complete Settlement',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            const amount = document.getElementById('settlement_amount').value;
            const remarks = document.getElementById('settlement_remarks').value;
            const fileInput = document.getElementById('final_settlement_document');
            const file = fileInput.files[0];

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/complete-final-settlement`;
            form.enctype = 'multipart/form-data';

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

            if (file) {
                // Append the original file input to the form
                fileInput.name = 'final_settlement_document';
                fileInput.style.display = 'none';
                form.appendChild(fileInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
