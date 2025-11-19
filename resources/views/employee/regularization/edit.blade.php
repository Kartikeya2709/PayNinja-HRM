@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">Review Regularization Request</div>

                <div class="card-body">
                    <p><strong>Employee:</strong> {{ $request->employee->name }}</p>
                    <p><strong>Date:</strong> {{ $request->date }}</p>
                    <p><strong>Check-in:</strong> {{ $request->check_in ?? 'N/A' }}</p>
                    <p><strong>Check-out:</strong> {{ $request->check_out ?? 'N/A' }}</p>
                    <p><strong>Reason:</strong> {{ $request->reason }}</p>

                    <hr>

                    <form action="{{ route('regularization-requests.update', $request->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="status">Action</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">Select Action</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>

                        <div class="form-group mb-3" id="attendance-status-group" style="display: none;">
                            <label for="attendance_status">Attendance Status</label>
                            <select name="attendance_status" id="attendance_status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="Present">Present</option>
                                <option value="Late">Late</option>
                                <option value="Half Day">Half Day</option>
                            </select>
                        </div>

                        <div class="form-group mb-3" id="rejection-reason-group" style="display: none;">
                            <label for="reason">Rejection Reason</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a href="{{ route('regularization-requests.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusSelect = document.getElementById('status');
        const attendanceStatusGroup = document.getElementById('attendance-status-group');
        const attendanceStatusSelect = document.getElementById('attendance_status');
        const rejectionReasonGroup = document.getElementById('rejection-reason-group');
        const rejectionReasonTextarea = document.getElementById('reason');

        function toggleFields() {
            if (statusSelect.value === 'approved') {
                attendanceStatusGroup.style.display = 'block';
                attendanceStatusSelect.required = true;
                rejectionReasonGroup.style.display = 'none';
                rejectionReasonTextarea.required = false;
            } else if (statusSelect.value === 'rejected') {
                attendanceStatusGroup.style.display = 'none';
                attendanceStatusSelect.required = false;
                rejectionReasonGroup.style.display = 'block';
                rejectionReasonTextarea.required = true;
            } else {
                attendanceStatusGroup.style.display = 'none';
                attendanceStatusSelect.required = false;
                rejectionReasonGroup.style.display = 'none';
                rejectionReasonTextarea.required = false;
            }
        }

        statusSelect.addEventListener('change', toggleFields);
        
        // Initial check in case of validation errors
        toggleFields();
    });
</script>
@endpush
