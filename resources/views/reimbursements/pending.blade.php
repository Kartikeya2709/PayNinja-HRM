@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">Pending Reimbursements</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('reimbursements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Reimbursement
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h4 class="alert-heading">Error!</h4>
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-clock text-warning"></i> 
                Reimbursements Awaiting Approval
                <span class="badge bg-warning text-dark ms-2">{{ $reimbursements->total() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            @if ($reimbursements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Title</th>
                                <th>Employee</th>
                                <th>Amount</th>
                                <th>Expense Date</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reimbursements as $reimbursement)
                                <tr>
                                    <td class="ps-4">
                                        <strong>{{ $reimbursement->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($reimbursement->description, 50) }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $reimbursement->employee->name ?? 'N/A' }}</strong>
                                            @if ($reimbursement->employee && $reimbursement->employee->designation)
                                                <br>
                                                <small class="text-muted">{{ $reimbursement->employee->designation->name ?? '' }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $reimbursement->company->currency_symbol ?? '₹' }}{{ number_format($reimbursement->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d M, Y') }}
                                    </td>
                                    <td>
                                        @if ($reimbursement->status === 'pending')
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-hourglass-start"></i> Pending
                                            </span>
                                        @elseif ($reimbursement->status === 'reporter_approved')
                                            <span class="badge bg-info text-white">
                                                <i class="fas fa-check-circle"></i> Reporter Approved
                                            </span>
                                        @elseif ($reimbursement->status === 'admin_approved')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-double"></i> Admin Approved
                                            </span>
                                        @elseif ($reimbursement->status === 'rejected')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle"></i> Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $reimbursement->created_at->diffForHumans() }}
                                        <br>
                                        <small class="text-muted">{{ $reimbursement->created_at->format('d M, Y H:i') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('reimbursements.show', Crypt::encrypt($reimbursement->id)) }}" 
                                               class="btn btn-info btn-sm" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-success btn-sm" 
                                                    title="Approve" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#approveModal"
                                                    data-reimbursement-id="{{ Crypt::encrypt($reimbursement->id) }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    title="Reject" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal"
                                                    data-reimbursement-id="{{ Crypt::encrypt($reimbursement->id) }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center p-4 border-top">
                    {{ $reimbursements->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">
                        <strong>No pending reimbursements</strong>
                    </p>
                    <p class="text-muted small">All reimbursements have been processed or no reimbursements are awaiting approval.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle"></i> Approve Reimbursement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approveRemarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="approveRemarks" name="remarks" rows="4" 
                                  placeholder="Enter approval remarks..." required></textarea>
                        <small class="form-text text-muted">Maximum 1000 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle"></i> Reject Reimbursement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        This action cannot be undone. Please provide clear reasons for rejection.
                    </div>
                    <div class="mb-3">
                        <label for="rejectRemarks" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectRemarks" name="remarks" rows="4" 
                                  placeholder="Enter rejection reason..." required></textarea>
                        <small class="form-text text-muted">Maximum 1000 characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle approve modal
        const approveModal = document.getElementById('approveModal');
        if (approveModal) {
            approveModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const reimbursementId = button.getAttribute('data-reimbursement-id');
                const form = document.getElementById('approveForm');
                form.action = `/reimbursements/${reimbursementId}/approve`;
            });
        }

        // Handle reject modal
        const rejectModal = document.getElementById('rejectModal');
        if (rejectModal) {
            rejectModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const reimbursementId = button.getAttribute('data-reimbursement-id');
                const form = document.getElementById('rejectForm');
                form.action = `/reimbursements/${reimbursementId}/reject`;
            });
        }

        // Clear remarks on modal close
        document.getElementById('approveModal')?.addEventListener('hidden.bs.modal', function() {
            document.getElementById('approveRemarks').value = '';
        });

        document.getElementById('rejectModal')?.addEventListener('hidden.bs.modal', function() {
            document.getElementById('rejectRemarks').value = '';
        });
    });
</script>
@endpush

@endsection
