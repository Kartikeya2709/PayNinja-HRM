@extends('layouts.app')

@section('title', 'Leave Calendar')

@section('css')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
.fc {
    max-width: 100%;
    margin: 0 auto;
}

.fc .fc-toolbar.fc-header-toolbar {
    margin-bottom: 1.5em;
}

.fc .fc-button-primary {
    background-color: #6777ef;
    border-color: #6777ef;
}

.fc .fc-button-primary:hover {
    background-color: #5a67d8;
    border-color: #5a67d8;
}

.fc .fc-button-primary:disabled {
    background-color: #95a0f4;
    border-color: #95a0f4;
}

.fc-event {
    cursor: pointer;
    padding: 2px 4px;
    margin: 1px 0;
    border-radius: 3px;
}

.fc-event-pending { 
    background-color: #ffc107; 
    border-color: #ffc107; 
    color: #000;
}

.fc-event-approved { 
    background-color: #28a745; 
    border-color: #28a745;
    color: #fff;
}

.fc-event-rejected { 
    background-color: #dc3545; 
    border-color: #dc3545;
    color: #fff;
}

.fc-event-cancelled { 
    background-color: #6c757d; 
    border-color: #6c757d;
    color: #fff;
}

.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f4f6f9;
    border-radius: 4px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.fc-day-today {
    background-color: #f8f9fa !important;
}

.fc-day-today .fc-daygrid-day-number {
    background-color: #6777ef;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 4px;
}

/* Filter styles */
.card-header {
    background-color: #f4f6f9;
    border-bottom: 1px solid #e3e6f0;
}

.form-group label {
    font-weight: 600;
    color: #34395e;
}

.select2-container--default .select2-selection--single {
    border-color: #e4e6fc;
    height: 42px;
    padding: 6px 12px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
}
</style>
@endsection

@section('content')
<div class="container">
<section class="section leave-calender">
    <div class="section-header">
        <h1>Leave Calendar</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Filter</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="department_filter">Department</label>
                                    <select id="department_filter" class="form-control select2">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status_filter">Status</label>
                                    <select id="status_filter" class="form-control select2">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="legend d-flex align-item-center gap-2">
                            <div class="legend-item">
                                <div class="legend-color"></div>
                                <span style="background-color: #ffc107;">Pending</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color"></div>
                                <span style="background-color: #28a745;">Approved</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color"></div>
                                <span style="background-color: #dc3545;">Rejected</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color"></div>
                                <span style="background-color: #6c757d;">Cancelled</span>
                            </div>
                        </div>

                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Leave Request Modal -->
<div class="modal fade" id="leaveRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Leave Request Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tr>
                            <th>Employee</th>
                            <td id="employeeName"></td>
                        </tr>
                        <tr>
                            <th>Department</th>
                            <td id="department"></td>
                        </tr>
                        <tr>
                            <th>Leave Type</th>
                            <td id="leaveType"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span id="status" class="badge"></span></td>
                        </tr>
                        <tr>
                            <th>Start Date</th>
                            <td id="startDate"></td>
                        </tr>
                        <tr>
                            <th>End Date</th>
                            <td id="endDate"></td>
                        </tr>
                        <tr>
                            <th>Total Days</th>
                            <td id="totalDays"></td>
                        </tr>
                        <tr>
                            <th>Reason</th>
                            <td id="reason"></td>
                        </tr>
                        <tr id="adminRemarksRow" style="display: none;">
                            <th>Admin Remarks</th>
                            <td id="adminRemarks"></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <div id="actionButtons" class="d-none">
                    <form id="approveForm" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">Approve</button>
                    </form>
                    <form id="rejectForm" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </form>
                </div>
                <a href="#" id="viewDetailsBtn" class="btn btn-primary">View Details</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2').select2();

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        events: function(info, successCallback, failureCallback) {
            var params = new URLSearchParams();
            var departmentId = $('#department_filter').val();
            var status = $('#status_filter').val();
            
            if (departmentId) params.append('department_id', departmentId);
            if (status) params.append('status', status);
            
            fetch("{{ route('company.leave-requests.calendar-events') }}?" + params.toString())
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            var event = info.event;
            var status = event.extendedProps.status;
            
            // Update modal content
            $('#employeeName').text(event.extendedProps.employeeName);
            $('#department').text(event.extendedProps.department);
            $('#leaveType').text(event.extendedProps.leaveType);
            $('#status')
                .text(status.charAt(0).toUpperCase() + status.slice(1))
                .removeClass()
                .addClass('badge badge-' + event.extendedProps.statusColor);
            $('#startDate').text(event.extendedProps.startDate);
            $('#endDate').text(event.extendedProps.endDate);
            $('#totalDays').text(event.extendedProps.totalDays);
            $('#reason').text(event.extendedProps.reason);
            
            if (event.extendedProps.adminRemarks) {
                $('#adminRemarksRow').show();
                $('#adminRemarks').text(event.extendedProps.adminRemarks);
            } else {
                $('#adminRemarksRow').hide();
            }
            
            // Show/hide action buttons for pending requests
            if (status === 'pending') {
                $('#actionButtons').removeClass('d-none');
                $('#approveForm').attr('action', "{{ url('company/leave-requests') }}/" + event.id + "/approve");
                $('#rejectForm').attr('action', "{{ url('company/leave-requests') }}/" + event.id + "/reject");
            } else {
                $('#actionButtons').addClass('d-none');
            }
            
            $('#viewDetailsBtn').attr('href', "{{ route('company.leave-requests.index') }}");
            $('#leaveRequestModal').modal('show');
        }
    });
    calendar.render();

    // Handle filter changes with debounce
    var filterTimeout;
    $('#department_filter, #status_filter').change(function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            calendar.refetchEvents();
        }, 300);
    });

    // Handle approve/reject forms
    $('#approveForm, #rejectForm').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();
        
        // Disable the button and show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#leaveRequestModal').modal('hide');
                calendar.refetchEvents();
                iziToast.success({
                    title: 'Success',
                    message: response.message,
                    position: 'topRight'
                });
            },
            error: function(xhr) {
                iziToast.error({
                    title: 'Error',
                    message: xhr.responseJSON.message || 'An error occurred',
                    position: 'topRight'
                });
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
@endpush
