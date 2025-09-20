@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
    <section class="section">
            <div class="section-header">
                <h1>Reimbursements</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="">Reimbursements</a></div>
                </div>
            </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-3">Reimbursement Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 detail-label">
                             <label><i class="fas fa-user"></i> Employee:</label>
                            <p>{{ $reimbursement->employee->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3 detail-label">
                            
                            <label> <i class="fas fa-wallet"></i>Amount:</label>
                            <p>₹{{ number_format($reimbursement->amount, 2) }}</p>
                        </div>
                        <div class="mb-3 detail-label">
                            <label> <i class="fas fa-tag"></i>Title:</label>
                            <p>{{ $reimbursement->title }}</p>
                        </div>
                        <div class="mb-3 detail-label">
                            <label> <i class="fas fa-align-left"></i>Description:</label>
                            <p>{{ $reimbursement->description }}</p>
                        </div>

                        @if($reimbursement->receipt_path)
                        <div class="mb-3 detail-label">
                            <label> <i class="fas fa-receipt"></i>Receipt:</label>
                            @php
                                $extension = strtolower(pathinfo($reimbursement->receipt_path, PATHINFO_EXTENSION));
                                $receiptUrl = asset('storage/' . $reimbursement->receipt_path);
                            @endphp
                            @if(in_array($extension, ['jpg', 'jpeg', 'png']))
                                <div>
                                    <img src="{{ $receiptUrl }}" alt="Receipt Image" class="img-fluid rounded border" style="max-width:300px; max-height:400px;" />
                                </div>
                                <a href="{{ $receiptUrl }}" target="_blank" class="btn btn-outline-secondary mt-2">View Full Image</a>
                            @elseif($extension === 'pdf')
                                <a href="{{ $receiptUrl }}" target="_blank" class="btn btn-outline-primary mt-2">View Receipt PDF</a>
                            @endif
                            <a href="{{ $receiptUrl }}" download class="btn btn-outline-secondary mt-2">Download Receipt</a>
                        </div>
                        @endif
                        <div class="mb-3 detail-label">
                            <label> <i class="fas fa-calendar-alt"></i>Expense Date:</label>
                            <p>{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('M d, Y') }}</p>
                        </div>
                        <div class="mb-3 detail-label">
                            <label><i class="fas fa-flag"></i>Status:</label>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'reporter_approved' => 'info',
                                    'admin_approved' => 'success',
                                    'approved' => 'success',
                                    'rejected' => 'danger'
                                ];
                                $statusText = [
                                    'pending' => 'Pending',
                                    'reporter_approved' => 'Approved by Reporter',
                                    'admin_approved' => 'Approved by Admin',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$reimbursement->status] ?? 'secondary' }}">
                                {{ $statusText[$reimbursement->status] ?? ucfirst($reimbursement->status) }}
                            </span>
                        </div>
                        <div class="mb-3 detail-label">
                            <label><i class="fas fa-calendar-plus"></i>Created At:</label>
                            <p>{{ $reimbursement->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                        
                        @if($reimbursement->status === 'approved')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Admin Remarks:</label>
                                <p>{{ $reimbursement->admin_remarks }}</p>
                            </div>
                        @endif
                        
                        @if($reimbursement->reporter_remarks)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Reporter Remarks:</label>
                                <p>{{ $reimbursement->reporter_remarks }}</p>
                            </div>
                        @endif
                        
                        @if($reimbursement->status === 'rejected')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rejection Reason:</label>
                                <p>{{ $reimbursement->remarks }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Actions</h4>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('reimbursements.index') }}" class="btn btn-primary mb-2">Back to List</a>
                        
                        @php
                            $user = Auth::user();
                            $isAdmin = $user->hasRole('admin');
                            $isCompanyAdmin = $user->hasRole('company_admin');
                            $isPrivileged = $isAdmin || $isCompanyAdmin;
                            $isReporter = $reimbursement->reporter_id && $user->id === optional($reimbursement->reporter)->user_id;
                            
                            // Admin/Company Admin can approve if status is pending or reporter_approved
                            // Reporter can only approve if status is pending
                            $canApprove = ($isPrivileged && in_array($reimbursement->status, ['pending', 'reporter_approved'])) || 
                                        ($isReporter && $reimbursement->status === 'pending');
                                        
                            $canReject = ($isPrivileged || $isReporter) && 
                                        in_array($reimbursement->status, ['pending', 'reporter_approved']);
                        @endphp

                        @if($canApprove)
                            <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ $isAdmin ? 'Approve as Admin' : 'Approve as Reporter' }}
                            </button>
                        @endif
                        
                        @if($canReject)
                            <button type="button" class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-ban me-1"></i> Reject
                            </button>
                        @endif
                        

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Approve Reimbursement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveForm" action="{{ route($isReporter ? 'reimbursements.approve-reporter' : 'reimbursements.approve', 
                          ['reimbursement' => $reimbursement->id]) }}" 
                      method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Please provide any additional remarks for this approval.
                        </div>
                        <div class="mb-3">
                            <label for="approvalRemarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                            <textarea name="remarks" id="approvalRemarks" 
                                    class="form-control" 
                                    rows="3" 
                                    placeholder="Enter approval remarks..." 
                                    required></textarea>
                            <div class="invalid-feedback">Please provide remarks for approval.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-1"></i> Confirm Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Reject Reimbursement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" action="{{ route('reimbursements.reject', ['reimbursement' => $reimbursement->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Please provide a clear reason for rejecting this reimbursement.
                        </div>
                        <div class="mb-3">
                            <label for="rejectionRemarks" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="remarks" id="rejectionRemarks" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Enter reason for rejection..." 
                                      required></textarea>
                            <div class="invalid-feedback">Please provide a reason for rejection.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-ban me-1"></i> Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        // Handle approve form submission
        $(document).on('submit', '#approveForm', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            
            // Validate form
            if (!form[0].checkValidity()) {
                form.addClass('was-validated');
                return false;
            }
            
            // Disable submit button and show loading state
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(response) {
                    // Show success message
                    showAlert('success', response.message || 'Reimbursement approved successfully');
                    // Close modal
                    $('#approveModal').modal('hide');
                    // Reload the page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    var errorMessage = xhr.responseJSON && xhr.responseJSON.message 
                        ? xhr.responseJSON.message 
                        : 'An error occurred while approving the reimbursement.';
                    showAlert('danger', errorMessage);
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Handle reject form submission
        $(document).on('submit', '#rejectForm', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            
            // Validate form
            if (!form[0].checkValidity()) {
                form.addClass('was-validated');
                return false;
            }
            
            // Disable submit button and show loading state
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(response) {
                    // Show success message
                    showAlert('success', response.message || 'Reimbursement rejected successfully');
                    // Close modal
                    $('#rejectModal').modal('hide');
                    // Reload the page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    var errorMessage = xhr.responseJSON && xhr.responseJSON.message 
                        ? xhr.responseJSON.message 
                        : 'An error occurred while rejecting the reimbursement.';
                    showAlert('danger', errorMessage);
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Clear form and reset validation when modal is hidden
        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $(this).find('form').removeClass('was-validated');
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();
        });
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        // Remove any existing alerts
        $('.alert-dismissible').remove();
        
        // Create and show the alert
        var alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        
        // Add the alert to the top of the content
        $('.container.mt-5').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert-dismissible').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
</script>
@endsection
