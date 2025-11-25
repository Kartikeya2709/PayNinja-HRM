@extends('attendance.layout')

@section('title', 'Attendance Dashboard')

@section('content')
<div class="container">
    <section class="section">
            <div class="section-header">
                <h1>Attendance Dashboard</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="">Attendance Dashboard</a></div>
                </div>
            </div>
<div class="row">
    <!-- Today's Status Card -->
    <div class="col-md-4 px-1 tab-width">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title text-center">Today's Status</h5>
                @if($todayAttendance)
                    <div class="text-center mt-3">
                        @if($todayAttendance->check_in && $todayAttendance->check_out)
                            <span class="badge bg-success p-3">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Checked Out
                            </span>
                        @elseif($todayAttendance->check_in)
                            <span class="badge bg-warning text-dark p-3">
                                <i class="bi bi-clock-history me-2"></i>
                                Checked In
                            </span>
                            <p class="mt-3">
                                <strong>Checked In:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}
                            </p>
                        @endif
                    </div>
                @else
                    <p class="text-muted my-4 today-status text-center">Not checked in today</p>
                @endif
                
                <div class="d-grid gap-2">
                    @if(!$todayAttendance || !$todayAttendance->check_in)
                        <button class="btn button btn-lg" id="checkInBtn">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Check In
                        </button>
                    @elseif(!$todayAttendance->check_out)
                        <button class="btn btn-danger btn-lg" id="checkOutBtn">
                            <i class="bi bi-box-arrow-right me-2"></i> Check Out
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- This Week's Summary -->
    <div class="col-md-8 px-1 mobile-space tab-width">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title text-center">This Week's Summary</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weekAttendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->date->format('D, M j') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $attendance->status === 'Present' ? 'success' : 
                                            ($attendance->status === 'Absent' ? 'danger' : 
                                            ($attendance->status === 'Late' ? 'warning' : 'info')) }}">
                                            {{ $attendance->status }}
                                        </span>
                                    </td>
                                    <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : '-' }}</td>
                                    <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : '-' }}</td>
                                    <td>
                                        @if($attendance->check_in && $attendance->check_out)
                                            @php
                                                $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                                $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                                $hours = $checkOut->diffInHours($checkIn);
                                                $minutes = $checkOut->diffInMinutes($checkIn) % 60;
                                            @endphp
                                            {{ sprintf('%d:%02d', $hours, $minutes) }} hrs
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No attendance records found for this week.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Summary -->
<div class="row mt-4">
    <div class="col-12 px-1">
        <div class="card attendance-card">
            <div class="card-body">
                <h5 class="card-title">Monthly Summary - {{ now()->format('F Y') }}</h5>
                <div class="row text-center mt-4">
                    <div class="col-md-3 mb-3">
                        <div class="card card-hover">
                            <div class="card-body d-flex align-items-center">
                                <div class="icon">
                                <i class="bi bi-check-circle-fill"></i></div>
                                <div class="text">
                                <h6 class="card-title">Present</h6>
                                <p>{{ $monthlySummary['present'] ?? 0 }}</p>
</div>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-hover">
                            <div class="card-body d-flex align-items-center">
                                <div class="icon">
                                <i class="bi bi-x-circle-fill"></i>
</div>
<div class="text">
                                <h6 class="card-title">Absent</h6>
                                <p>{{ $monthlySummary['absent'] ?? 0 }}</p>
                            </div>
</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-hover">
                            <div class="card-body d-flex align-items-center">
                                 <div class="icon">
                                    <i class="bi bi-clock-fill"></i>
</div>
<div class="text">
                                <h6 class="card-title">Late</h6>
                                <p>{{ $monthlySummary['late'] ?? 0 }}</p>
</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-hover">
                            <div class="card-body d-flex align-items-center">
                                <div class="icon">
                                    <i class="bi bi-calendar-event-fill"></i></div>
                                    <div class="text">
                                <h6 class="card-title">On Leave</h6>
                                <p>{{ $monthlySummary['on_leave'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle check in
    $('#checkInBtn').click(function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const location = position.coords.latitude + ',' + position.coords.longitude;
                checkInOut('check-in', location);
            }, function() {
                checkInOut('check-in');
            });
        } else {
            checkInOut('check-in');
        }
    });

    // Handle check out
    $('#checkOutBtn').click(function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const location = position.coords.latitude + ',' + position.coords.longitude;
                checkInOut('check-out', location);
            }, function() {
                checkInOut('check-out');
            });
        } else {
            checkInOut('check-out');
        }
    });

    function checkInOut(type, location = null) {
        const url = type === 'check-in' ? '{{ route("check-in.post") }}' : '{{ route("check-out.post") }}';
        const token = $('meta[name="csrf-token"]').attr('content');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: token,
                location: location
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.message || 'An error occurred. Please try again.');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                alert(error.message || 'An error occurred. Please try again.');
            }
        });
    }
});
</script>
@endpush
