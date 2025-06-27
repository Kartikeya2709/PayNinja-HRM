@extends('layouts.app')

@section('title', 'Attendance Management')

@push('styles')
<style>
    .department-header {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .department-header:hover {
        background-color: #f8f9fa;
    }
    .employee-details {
        display: none;
    }
    .employee-details.show {
        display: table-row;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .no-attendance {
        color: #6c757d;
        font-style: italic;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card attendance">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Attendance Summary</h4>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-white text-primary fs-6">{{ $today->format('l, F j, Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($departmentSummary->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%">Department</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Late</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-end" style="width: 100px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departmentSummary as $dept)
                                        @php
                                            $deptId = 'dept-' . $dept->id;
                                            $hasEmployees = $dept->employees->isNotEmpty();
                                        @endphp
                                        <tr class="department-header" data-bs-toggle="collapse" data-bs-target="#{{ $deptId }}" aria-expanded="false" aria-controls="{{ $deptId }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($hasEmployees)
                                                        <i class="bi bi-chevron-right me-2 toggle-icon"></i>
                                                    @else
                                                        <span class="ms-4"></span>
                                                    @endif
                                                    <strong>{{ $dept->name }}</strong>
                                                </div>
                                            </td>
                                            <td class="text-center text-success">{{ $dept->present_count }}</td>
                                            <td class="text-center text-danger">{{ $dept->absent_count }}</td>
                                            <td class="text-center text-warning">{{ $dept->late_count }}</td>
                                            <td class="text-center">{{ $dept->total_employees }}</td>
                                            <td class="text-end">
                                                @if($hasEmployees)
                                                    <span class="badge bg-light text-dark">
                                                        {{ $dept->employees->count() }} {{ Str::plural('employee', $dept->employees->count()) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        
                                        @if($hasEmployees)
                                            <tr class="employee-details collapse" id="{{ $deptId }}">
                                                <td colspan="6" class="p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-hover mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width: 5%">#</th>
                                                                    <th style="width: 22%">Employee</th>
                                                                    <th style="width: 22%">Designation</th>
                                                                    <th class="text-center">Status</th>
                                                                    <th class="text-center">Check In</th>
                                                                    <th class="text-center">Check Out</th>
                                                                    <th class="text-center">Working Hours</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($dept->employees as $index => $employee)
                                                                    @php
                                                                        $attendance = $employee->attendances->first();
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="avatar avatar-sm me-2">
                                                                                    <img src="{{ $employee->avatar ? asset('storage/' . $employee->avatar) : asset('assets/img/avatar.png') }}" 
                                                                                         alt="{{ $employee->name }}" 
                                                                                         class="rounded-circle"
                                                                                         style="width: 32px; height: 32px; object-fit: cover;">
                                                                                </div>
                                                                                <div>
                                                                                    <div class="fw-medium">{{ $employee->name }}</div>
                                                                                    <div class="text-muted small">{{ $employee->employee_id ?? 'N/A' }}</div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>{{ $employee->designation->name ?? 'N/A' }}</td>
                                                                        <td class="text-center">
                                                                            @if($attendance)
                                                                                @php
                                                                                    $statusClass = [
                                                                                        'Present' => 'success',
                                                                                        'Absent' => 'danger',
                                                                                        'Late' => 'warning',
                                                                                        'On Leave' => 'info',
                                                                                        'Half Day' => 'primary'
                                                                                    ][$attendance->status] ?? 'secondary';
                                                                                @endphp
                                                                                <span class="badge bg-{{ $statusClass }} status-badge">
                                                                                    {{ $attendance->status }}
                                                                                </span>
                                                                            @else
                                                                                <span class="badge bg-light text-dark status-badge">
                                                                                    Not Recorded
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($attendance && $attendance->check_in)
                                                                                {{ \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') }}
                                                                            @else
                                                                                --:--
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($attendance && $attendance->check_out)
                                                                                {{ \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') }}
                                                                            @else
                                                                                --:--
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($attendance && $attendance->check_in && $attendance->check_out)
                                                                                @php
                                                                                    $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                                                                    $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                                                                    $hours = $checkOut->diffInHours($checkIn);
                                                                                    $minutes = $checkOut->diffInMinutes($checkIn) % 60;
                                                                                @endphp
                                                                                {{ sprintf('%d hrs %02d mins', $hours, $minutes) }}
                                                                            @else
                                                                                --:--
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-center text-success">{{ $totalPresent }}</th>
                                        <th class="text-center text-danger">{{ $totalAbsent }}</th>
                                        <th class="text-center text-warning">{{ $totalLate }}</th>
                                        <th class="text-center">{{ $totalEmployees }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-4">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                No attendance data available for today.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all collapses
        const collapses = document.querySelectorAll('.collapse');
        
        // Handle department header clicks
        document.querySelectorAll('.department-header').forEach(header => {
            const targetId = header.getAttribute('data-bs-target');
            const targetCollapse = document.querySelector(targetId);
            const icon = header.querySelector('.toggle-icon');
            
            if (!targetCollapse || !icon) return;
            
            // Initialize collapse with toggle false to prevent auto-show
            const bsCollapse = new bootstrap.Collapse(targetCollapse, {
                toggle: false
            });
            
            // Toggle the collapse when header is clicked
            header.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Close all other open collapses
                collapses.forEach(collapse => {
                    if (collapse !== targetCollapse && collapse.classList.contains('show')) {
                        const otherBsCollapse = bootstrap.Collapse.getInstance(collapse);
                        if (otherBsCollapse) {
                            otherBsCollapse.hide();
                        }
                    }
                });
                
                // Toggle the clicked collapse
                bsCollapse.toggle();
                
                // Update icon
                if (targetCollapse.classList.contains('show')) {
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                } else {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                }
            });
            
            // Update icon when collapse is shown/hidden by other means
            targetCollapse.addEventListener('show.bs.collapse', function() {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            });
            
            targetCollapse.addEventListener('hide.bs.collapse', function() {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            });
        });
    });
</script>
@endpush
