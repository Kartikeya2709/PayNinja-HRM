@extends('layouts.app')

@section('title', 'Attendance Management')

@push('styles')
<style>
/* ===== Department Table ===== */
.department-header {
    cursor: pointer;
    transition: background .2s;
}
.department-header:hover {
    background: #f8fafc;
}
.department-row td {
    vertical-align: middle;
    font-weight: 600;
}

/* ===== Employee Table ===== */
.employee-details td {
    background: #fdfefe;
}
.employee-table {
    border-top: 1px solid #e9ecef;
}
.employee-table thead th {
    font-size: 12px;
    text-transform: uppercase;
    color: #6c757d;
    background: #f8f9fa;
}
.employee-table tbody td {
    font-size: 13px;
    vertical-align: middle;
}

/* ===== Misc ===== */
.toggle-icon {
    transition: transform .2s ease;
}
.status-badge {
    font-size: 11px;
    padding: 4px 8px;
}
</style>
@endpush

@section('content')
<div class="container">
<section class="section">

    <div class="section-header">
        <h1>Attendance Summary</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('home') }}">Dashboard</a>
            </div>
            <div class="breadcrumb-item">Attendance Summary</div>
        </div>
    </div>

    <div class="row">
    <div class="col-12">
    <div class="card">

        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Attendance Summary</h5>
            <span class="btn btn-primary">
                {{ $today->format('l, F j, Y') }}
            </span>
        </div>

        <div class="card-body p-0">
        @if($departmentSummary->isNotEmpty())

        <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">

            <thead class="table-light">
                <tr>
                    <th width="35%">Department</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Late</th>
                    <th class="text-center">Total</th>
                    <th width="120"></th>
                </tr>
            </thead>

            <tbody>
            @foreach($departmentSummary as $dept)
                @php
                    $deptId = 'dept-'.$dept->id;
                    $hasEmployees = $dept->employees->isNotEmpty();
                @endphp

                <!-- ===== Department Row ===== -->
                <tr class="department-header department-row" data-target="#{{ $deptId }}">
                    <td>
                        @if($hasEmployees)
                            <i class="bi bi-chevron-right toggle-icon me-2"></i>
                        @else
                            <span class="me-3"></span>
                        @endif
                        {{ $dept->name }}
                    </td>
                    <td class="text-center text-success">{{ $dept->present_count }}</td>
                    <td class="text-center text-danger">{{ $dept->absent_count }}</td>
                    <td class="text-center text-warning">{{ $dept->late_count }}</td>
                    <td class="text-center">{{ $dept->total_employees }}</td>
                    <td class="text-end">
                        @if($hasEmployees)
                        <span class="badge bg-light text-dark">
                            {{ $dept->employees->count() }}
                            {{ Str::plural('employee', $dept->employees->count()) }}
                        </span>
                        @endif
                    </td>
                </tr>

                <!-- ===== Employee Details ===== -->
                @if($hasEmployees)
                <tr class="collapse employee-details" id="{{ $deptId }}">
                    <td colspan="6" class="p-0">
                        <div class="table-responsive">
                        <table class="table table-sm mb-0 employee-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Designation</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Check In</th>
                                    <th class="text-center">Check Out</th>
                                    <th class="text-center">Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($dept->employees as $i => $employee)
                                @php
                                    $attendance = $employee->attendances->first();
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $employee->avatar ? asset('storage/'.$employee->avatar) : asset('assets/img/avatar.png') }}"
                                                 class="rounded-circle me-2"
                                                 width="32" height="32">
                                            <div>
                                                <div class="fw-semibold">{{ $employee->name }}</div>
                                                <small class="text-muted">
                                                    {{ $employee->employee_id ?? 'N/A' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $employee->designation->name ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($attendance)
                                            @php
                                                $cls = [
                                                    'Present'=>'success',
                                                    'Absent'=>'danger',
                                                    'Late'=>'warning',
                                                    'On Leave'=>'info',
                                                    'Half Day'=>'primary'
                                                ][$attendance->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $cls }} status-badge">
                                                {{ $attendance->status }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary status-badge">
                                                N/A
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $attendance?->check_in
                                            ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A')
                                            : '--:--' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $attendance?->check_out
                                            ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A')
                                            : '--:--' }}
                                    </td>
                                    <td class="text-center">
                                        @if($attendance?->check_in && $attendance?->check_out)
                                            @php
                                                $in = \Carbon\Carbon::parse($attendance->check_in);
                                                $out = \Carbon\Carbon::parse($attendance->check_out);
                                            @endphp
                                            {{ $out->diffInHours($in) }}h
                                            {{ $out->diffInMinutes($in) % 60 }}m
                                        @else
                                            --
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
            <div class="alert alert-info text-center m-4">
                No attendance data available for today.
            </div>
        @endif
        </div>

    </div>
    </div>
    </div>

</section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.department-header').forEach(header => {
        const target = document.querySelector(header.dataset.target);
        const icon = header.querySelector('.toggle-icon');
        if (!target || !icon) return;

        const collapse = new bootstrap.Collapse(target, { toggle: false });

        header.addEventListener('click', () => {
            // close others
            document.querySelectorAll('.employee-details.show').forEach(open => {
                if (open !== target) {
                    bootstrap.Collapse.getInstance(open)?.hide();
                }
            });
            collapse.toggle();
        });

        target.addEventListener('show.bs.collapse', () => {
            icon.classList.replace('bi-chevron-right', 'bi-chevron-down');
        });

        target.addEventListener('hide.bs.collapse', () => {
            icon.classList.replace('bi-chevron-down', 'bi-chevron-right');
        });
    });

});
</script>
@endpush
