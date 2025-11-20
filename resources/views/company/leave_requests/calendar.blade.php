@extends('layouts.app')

@section('title', 'Leave Calendar')

@section('css')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<!-- Font Awesome for arrows -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

<style>
/* Calendar Layout */
.fc {
    max-width: 100%;
    margin: 0 auto;
}

/* Header Toolbar - single line */
.fc .fc-toolbar.fc-header-toolbar {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: nowrap !important; /* no line breaks */
    gap: 10px !important;
    margin-bottom: 1.2rem !important;
}

/* Buttons - Glassy Style */
.fc .fc-button {
    background: rgba(103, 119, 239, 0.9) !important;
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.2) !important;
    color: #fff !important;
    padding: 6px 14px !important;
    border-radius: 8px !important;
    margin: 0 3px !important;
    font-size: 14px !important;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.fc .fc-button:hover {
    background: rgba(90, 103, 216, 1) !important;
    transform: translateY(-1px);
}
.fc .fc-button:disabled {
    background: rgba(149, 160, 244, 0.6) !important;
    opacity: 0.7 !important;
    cursor: not-allowed !important;
}

/* Title Styling */
.fc .fc-toolbar-title {
    font-size: 1.4rem !important;
    font-weight: 700 !important;
    color: #222 !important;
    margin: 0 10px;
}

/* Events */
.fc-event { cursor: pointer; border-radius: 3px; padding: 2px 4px; }
.fc-event-pending { background-color: #ffc107; color: #000; }
.fc-event-approved { background-color: #28a745; color: #fff; }
.fc-event-rejected { background-color: #dc3545; color: #fff; }
.fc-event-cancelled { background-color: #6c757d; color: #fff; }

/* Legend */
.legend {
    display: flex; flex-wrap: wrap; gap: 1rem;
    margin-bottom: 1.5rem; padding: 1rem;
    background: #f4f6f9; border-radius: 4px;
}
.legend-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; }
.legend-color {
    width: 20px; height: 20px;
    border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Today Highlight */
.fc-day-today { background-color: #f8f9fa !important; }
.fc-day-today .fc-daygrid-day-number {
    background: #6777ef; color: #fff;
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 4px auto;
}
</style>
@endsection

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>Leave Calendar</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="">Leave Calendar</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- Filters -->
                    <div class="card-header justify-content-center mb-3 margin-bottom"><h4>Filter</h4></div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Department -->
                            <div class="col-md-6">
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
                            <!-- Status -->
                            <div class="col-md-6">
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

                        <!-- Legend -->
                       <div class="legend mt-4">
  <div class="legend-item">
    <span class="legend-dot pending"></span>
    <span class="legend-label">Pending</span>
  </div>
  <div class="legend-item">
    <span class="legend-dot approved"></span>
    <span class="legend-label">Approved</span>
  </div>
  <div class="legend-item">
    <span class="legend-dot rejected"></span>
    <span class="legend-label">Rejected</span>
  </div>
  <div class="legend-item">
    <span class="legend-dot cancelled"></span>
    <span class="legend-label">Cancelled</span>
  </div>
</div>


                        <!-- Calendar -->
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="leaveRequestModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Leave Request Details</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <table class="table table-striped">
          <tr><th>Employee</th><td id="employeeName"></td></tr>
          <tr><th>Department</th><td id="department"></td></tr>
          <tr><th>Leave Type</th><td id="leaveType"></td></tr>
          <tr><th>Status</th><td><span id="status" class="badge"></span></td></tr>
          <tr><th>Start Date</th><td id="startDate"></td></tr>
          <tr><th>End Date</th><td id="endDate"></td></tr>
          <tr><th>Total Days</th><td id="totalDays"></td></tr>
          <tr><th>Reason</th><td id="reason"></td></tr>
          <tr id="adminRemarksRow" style="display:none;">
            <th>Admin Remarks</th><td id="adminRemarks"></td>
          </tr>
        </table>
      </div>
      <div class="modal-footer">
        <div id="actionButtons" class="d-none">
          <form id="approveForm" class="d-inline">@csrf
            <button type="submit" class="btn btn-success">Approve</button>
          </form>
          <form id="rejectForm" class="d-inline">@csrf
            <button type="submit" class="btn btn-danger">Reject</button>
          </form>
        </div>
        <a href="#" id="viewDetailsBtn" class="btn btn-primary">View Details</a>
        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- jQuery (needed for select2 + modal) -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- FullCalendar Core -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('.select2').select2();

    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonIcons: {
  prev: 'fas fa-chevron-left',
  next: 'fas fa-chevron-right'
},
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List'
        },
        events: function (info, success, failure) {
            let params = new URLSearchParams();
            if ($('#department_filter').val()) params.append('department_id', $('#department_filter').val());
            if ($('#status_filter').val()) params.append('status', $('#status_filter').val());

            fetch("{{ route('leave-requests.calendar-events') }}?" + params.toString())
                .then(res => res.json())
                .then(data => success(data))
                .catch(err => failure(err));
        },
        eventClick: function (info) {
            var e = info.event.extendedProps;

            $('#employeeName').text(e.employeeName);
            $('#department').text(e.department);
            $('#leaveType').text(e.leaveType);
            $('#status').text(e.status.charAt(0).toUpperCase() + e.status.slice(1))
                        .removeClass().addClass('badge badge-' + e.statusColor);
            $('#startDate').text(e.startDate);
            $('#endDate').text(e.endDate);
            $('#totalDays').text(e.totalDays);
            $('#reason').text(e.reason);

            if (e.adminRemarks) { $('#adminRemarksRow').show(); $('#adminRemarks').text(e.adminRemarks); }
            else { $('#adminRemarksRow').hide(); }

            if (e.status === 'pending') {
                $('#actionButtons').removeClass('d-none');
                $('#approveForm').attr('action', "{{ url('company/leave-requests') }}/" + info.event.id + "/approve");
                $('#rejectForm').attr('action', "{{ url('company/leave-requests') }}/" + info.event.id + "/reject");
            } else {
                $('#actionButtons').addClass('d-none');
            }

            $('#viewDetailsBtn').attr('href', "{{ route('leave-requests.index') }}");
            $('#leaveRequestModal').modal('show');
        }
    });
    calendar.render();

    // Refetch when filters change
    $('#department_filter, #status_filter').change(() => calendar.refetchEvents());
});
</script>
@endpush
