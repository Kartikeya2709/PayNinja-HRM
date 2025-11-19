// resources/views/admin/attendance/index.blade.php
@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attendance Records</h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Record
                        </button>
                        <button type="button" class="btn btn-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-upload me-1"></i> Import
                        </button>
                        <a href="{{ route('admin-attendance.export') }}" class="btn btn-info btn-sm ms-2">
                            <i class="bi bi-download me-1"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="date_range" class="form-label">Date Range</label>
                                <input type="text" class="form-control daterange" id="date_range" name="date_range" 
                                    value="{{ request('date_range') }}" autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <label for="employee_id" class="form-label">Employee</label>
                                <select class="form-select" id="employee_id" name="employee_id">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->user->name }} ({{ $emp->employee_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="Present" {{ request('status') == 'Present' ? 'selected' : '' }}>Present</option>
                                    <option value="Absent" {{ request('status') == 'Absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="Late" {{ request('status') == 'Late' ? 'selected' : '' }}>Late</option>
                                    <option value="On Leave" {{ request('status') == 'On Leave' ? 'selected' : '' }}>On Leave</option>
                                    <option value="Half Day" {{ request('status') == 'Half Day' ? 'selected' : '' }}>Half Day</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel me-1"></i> Filter
                                </button>
                                <a href="{{ route('admin-attendance.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Employee ID</th>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th>Hours</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td>{{ $loop->iteration + (($attendances->currentPage() - 1) * $attendances->perPage()) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <img src="{{ $attendance->employee->user->profile_photo_url }}" 
                                                         alt="{{ $attendance->employee->user->name }}" 
                                                         class="rounded-circle">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $attendance->employee->user->name }}</h6>
                                                    <small class="text-muted">{{ $attendance->employee->designation->name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $attendance->employee->employee_id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                                        <td>
                                            @if($attendance->check_in)
                                                {{ \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') }}
                                                @if($attendance->check_in_location)
                                                    <i class="bi bi-geo-alt-fill text-primary ms-1" 
                                                       data-bs-toggle="tooltip" 
                                                       title="{{ $attendance->check_in_location }}"
                                                       onclick="showLocation({{ $attendance->check_in_lat }}, {{ $attendance->check_in_lng }}, '{{ $attendance->check_in_location }}')"></i>
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
                                                       title="{{ $attendance->check_out_location }}"
                                                       onclick="showLocation({{ $attendance->check_out_lat }}, {{ $attendance->check_out_lng }}, '{{ $attendance->check_out_location }}')"></i>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ \App\Helpers\AttendanceHelper::getStatusBadgeClass($attendance->status) }}">
                                                {{ $attendance->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->check_in && $attendance->check_out)
                                                {{ \Carbon\Carbon::parse($attendance->check_out)->diffInHours(\Carbon\Carbon::parse($attendance->check_in)) }} hrs
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary edit-attendance" 
                                                        data-id="{{ $attendance->id }}"
                                                        data-bs-toggle="tooltip" 
                                                        title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger delete-attendance"
                                                        data-id="{{ $attendance->id }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No attendance records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of {{ $attendances->total() }} entries
                        </div>
                        {{ $attendances->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Attendance Modal -->
@include('admin.attendance.modals.add')

<!-- Edit Attendance Modal -->
@include('admin.attendance.modals.edit')

<!-- Import Modal -->
@include('admin.attendance.modals.import')

<!-- Location Modal -->
@include('admin.attendance.modals.location')

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .table th { white-space: nowrap; }
    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        overflow: hidden;
    }
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .badge { font-size: 0.75rem; }
    .bi-geo-alt-fill { cursor: pointer; }
    .bi-geo-alt-fill:hover { opacity: 0.8; }
    .pagination { margin-bottom: 0; }
</style>
@endpush

@push('scripts')
<script src="[https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>](https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>)
<script src="[https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>](https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>)
@if(config('services.google.maps_key'))
<script src="[https://maps.googleapis.com/maps/api/js?key={{](https://maps.googleapis.com/maps/api/js?key={{) config('services.google.maps_key') }}&libraries=places"></script>
@endif

<script>
$(document).ready(function() {
    // Initialize date range picker
    $('.daterange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'YYYY-MM-DD',
            cancelLabel: 'Clear'
        },
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days