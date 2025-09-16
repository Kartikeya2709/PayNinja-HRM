@extends('layouts.app')

@section('title', 'Leave Calendar')

@section('css')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<style>

</style>
@endsection

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>Leave Calendar</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Leave Calendar</div>
        </div>
    </div>
 <div class="col-lg-12 px-1 mobile-space">
        <div class="card emp-calender">
            <div class="card-header">
                <h5>Calendar</h5>
            </div>
            <div class="card-body">
                <div id="employeeCalendar"></div>
            </div>
        </div>
    </div>
</div>
  
           
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('employeeCalendar');
  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
   
  });
  calendar.render();
});
</script>

@endpush