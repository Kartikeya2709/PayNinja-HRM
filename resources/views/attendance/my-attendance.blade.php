@extends('attendance.layout')

@section('title', 'My Attendance History')

@section('content')
<div class="container">
<div class="row">
    <div class="col-12">
        <div class="card my-attendance">
            <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">My Attendance</h6>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <form method="GET" class="d-flex gap-2">
                        <input type="month" name="month" class="form-control" value="{{ $month }}" onchange="this.form.submit()">
                        <a href="{{ route('attendance.export', ['month' => $month]) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('attendance.exportPdf', ['month' => $month]) }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </form>
                    <a href="{{ route('attendance.my-attendance') }}?export=true&month={{ $month ?? now()->format('Y-m') }}" class="btn btn-info">
                        <i class="bi bi-download me-1"></i> Export
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Hours</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('l') }}</td>
                                    <td>
                                        @if($attendance->check_in)
                                            {{ \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') }}
                                            @if($attendance->check_in_location)
                                                <i class="bi bi-geo-alt-fill text-primary ms-1" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ $attendance->check_in_location }}"></i>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->check_out)
                                            {{ \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') }}
                                            @if($attendance->check_out_location)
                                                <i class="bi bi-geo-alt-fill text-primary ms-1" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ $attendance->check_out_location }}"></i>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $attendance->status === 'Present' ? 'success' : 
                                            ($attendance->status === 'Absent' ? 'danger' : 
                                            ($attendance->status === 'Late' ? 'warning' : 
                                            ($attendance->status === 'On Leave' ? 'info' : 'secondary'))) 
                                        }}">
                                            {{ $attendance->status }}
                                        </span>
                                    </td>
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
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->remarks)
                                            <span data-bs-toggle="tooltip" title="{{ $attendance->remarks }}">
                                                {{ \Illuminate\Support\Str::limit($attendance->remarks, 30) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No attendance records found for this month.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        @if($attendances->count() > 0)
                            Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of {{ $attendances->total() }} entries
                        @endif
                    </div>
                    {{ $attendances->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Summary -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Summary - {{ \Carbon\Carbon::parse($month ?? now())->format('F Y') }}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
<<<<<<< Updated upstream
                    <!-- <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
=======
                    <div class="col-md-3 mb-3">
                        <div class="card action-icon month-sum text-white">
>>>>>>> Stashed changes
                            <div class="card-body">
                                <h6 class="card-title">Working Days</h6>
                                <h2>{{ $monthlySummary['total_working_days'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div> -->
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Week-Off</h6>
                                <h2>{{ $monthlySummary['week_off'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card action-icon month-sum text-white">
                            <div class="card-body">
                                <h6 class="card-title">Present</h6>
                                <h2>{{ $monthlySummary['present'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card action-icon month-sum text-white">
                            <div class="card-body">
                                <h6 class="card-title">Absent</h6>
                                <h2>{{ $monthlySummary['absent'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card action-icon month-sum text-dark">
                            <div class="card-body">
                                <h6 class="card-title">Late</h6>
                                <h2>{{ $monthlySummary['late'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="card action-icon month-sum text-white">
                            <div class="card-body">
                                <h6 class="card-title">On Leave</h6>
                                <h2>{{ $monthlySummary['on_leave'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card action-icon month-sum text-white">
                            <div class="card-body">
                                <h6 class="card-title">Half Day</h6>
                                <h2>{{ $monthlySummary['half_day'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Holiday</h6>
                                <h2>{{ $monthlySummary['holiday'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Attendance Rate</h6>
                                @php
                                    $workingDays = $monthlySummary['total_working_days'] ?? 0;
                                    $daysWorked = $monthlySummary['days_worked'] ?? 0;
                                    $attendanceRate = $workingDays > 0 ? ($daysWorked / $workingDays) * 100 : 0;
                                @endphp
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $attendanceRate }}%;" 
                                         aria-valuenow="{{ $attendanceRate }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($attendanceRate, 1) }}%
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
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
