@extends('layouts.app')

@section('content')
<div class="container">
     <section class="section">
            <div class="section-header">
                <h1>Regularization Requests</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="">Regularization Requests</div>
                </div>
            </div>
    <div class="row">
        <div class="col-md-12">
        
                
                    <div class="card">
                        <div class="card-1">
                <h5 class="card-title mb-0">Attendance Regularization Requests</h5>
                    
               
                    <div class="">
                    @if (!is_null(Auth::user()->employee->reporting_manager_id))
                        <a href="{{ route('regularization.requests.create') }}" class="btn btn-primary">New Request</a></div>
</div>
</div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(is_null(Auth::user()->employee->reporting_manager_id))
                    <form action="{{ route('regularization.requests.bulk-update') }}" method="POST" id="bulk-action-form">
                        @csrf
                       
                            <button type="button" id="bulk-approve-btn" class="btn btn-success">Approve Selected</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">Reject Selected</button>
                    
</div>
</div>
                    @endif

                    @if (isset($pending_requests))
    <ul class="nav nav-tabs Attendance-Regularization" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true">Pending</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="false">Approved</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false">Rejected</button>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
            @include('employee.regularization._request_table', ['requests' => $pending_requests, 'show_actions' => true, 'pagination_name' => 'pending_page'])
        </div>
        <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
            @include('employee.regularization._request_table', ['requests' => $approved_requests, 'show_actions' => false, 'pagination_name' => 'approved_page'])
        </div>
        <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
            @include('employee.regularization._request_table', ['requests' => $rejected_requests, 'show_actions' => false, 'pagination_name' => 'rejected_page'])
        </div>
    </div>
@else
    @include('employee.regularization._request_table', ['requests' => $requests, 'show_actions' => false])
@endif

                    @if(is_null(Auth::user()->employee->reporting_manager_id))
                    </form>
                    @endif

                    @if (isset($requests) && $requests instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ $requests->links() }}
                    @endif

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="approvalModalLabel">Approve Requests</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label for="bulk_attendance_status">Attendance Status</label>
            <select name="attendance_status" id="bulk_attendance_status" class="form-control" required>
                <option value="">Select Status</option>
                <option value="Present">Present</option>
                <option value="Late">Late</option>
                <option value="Half Day">Half Day</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirm-approval-btn" class="btn btn-success">Confirm Approval</button>
      </div>
    </div>
  </div>
</div>
                </div>
</section>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(is_null(Auth::user()->employee->reporting_manager_id))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select-all checkbox logic
        const selectAll = document.getElementById('select-all');
        if (selectAll) {
            selectAll.addEventListener('click', function(event) {
                let checkboxes = document.querySelectorAll('input[name="request_ids[]"]');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = event.target.checked;
                });
            });
        }

        const bulkApproveBtn = document.getElementById('bulk-approve-btn');
        const confirmApprovalBtn = document.getElementById('confirm-approval-btn');
        const bulkActionForm = document.getElementById('bulk-action-form');
        const approvalModal = new bootstrap.Modal(document.getElementById('approvalModal'));

        if (bulkApproveBtn) {
            bulkApproveBtn.addEventListener('click', function() {
                const selectedIds = Array.from(document.querySelectorAll('input[name="request_ids[]"]:checked')).map(cb => cb.value);
                if (selectedIds.length === 0) {
                    alert('Please select at least one request to approve.');
                    return;
                }
                approvalModal.show();
            });
        }

        if (confirmApprovalBtn) {
            confirmApprovalBtn.addEventListener('click', function() {
                const attendanceStatusSelect = document.getElementById('bulk_attendance_status');
                if (attendanceStatusSelect.value === '') {
                    alert('Please select an attendance status.');
                    return;
                }

                // Add action and status to the form and submit
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'approve';
                bulkActionForm.appendChild(actionInput);

                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'attendance_status';
                statusInput.value = attendanceStatusSelect.value;
                bulkActionForm.appendChild(statusInput);

                bulkActionForm.submit();
            });
        }
    });
</script>
@endif
@endpush
