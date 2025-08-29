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
</style>
@endsection

@section('content')
<section class="section container">

    <div class="section-header">
        <h1>Leave Calendar</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}"></a>Dashboard</div>
            <div class="breadcrumb-item active"> <a href="">Leave Calendar</a></div>
        </div>
    </div>

    <div class="card">
        
        <h1>Leave Calendar</h1>
    

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
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
                <a href="#" id="viewDetailsBtn" class="btn btn-primary">View Details</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        events: "{{ route('employee.leave-requests.calendar-events') }}",
        eventClick: function(info) {
            var event = info.event;
            var status = event.extendedProps.status;
            
            // Update modal content
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
            
            $('#viewDetailsBtn').attr('href', event.extendedProps.detailsUrl);
            $('#leaveRequestModal').modal('show');
        }
    });
    calendar.render();
});
</script>
@endpush
