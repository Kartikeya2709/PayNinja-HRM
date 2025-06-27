@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="container-fluid">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
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
                        <div class="btn-group ms-2">
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item export-btn" href="#" data-type="excel"><i class="bi bi-file-earmark-excel me-2"></i>Export to Excel</a></li>
                                <li><a class="dropdown-item export-btn" href="#" data-type="pdf"><i class="bi bi-file-pdf me-2"></i>Export to PDF</a></li>
                            </ul>
                        </div>
                        <a href="{{ route('admin.attendance.template') }}" class="btn btn-outline-secondary btn-sm ms-2" title="Download Import Template">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Template
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body p-3">
                            <form id="filterForm" method="GET">
                                <div class="row g-2 align-items-end justify-content-space-between">
                                    <div class="col-md-4">
                                        <label for="date_range" class="form-label small mb-1">Date Range</label>
                                        <input type="text" class="form-control form-control-sm daterange" id="date_range" name="date_range" 
                                            value="{{ request('date_range') }}" autocomplete="off" placeholder="Select date range">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="employee_id" class="form-label small mb-1">Employee</label>
                                        <select class="form-select form-select-sm" id="employee_id" name="employee_id">
                                            <option value="">All Employees</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                                    {{ $emp->user->name }} ({{ $emp->employee_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="department_id" class="form-label small mb-1">Department</label>
                                        <select class="form-select form-select-sm" id="department_id" name="department_id">
                                            <option value="">All Departments</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="status" class="form-label small mb-1">Status</label>
                                        <select class="form-select form-select-sm" id="status" name="status">
                                            <option value="">All Status</option>
                                            <option value="Present" {{ request('status') == 'Present' ? 'selected' : '' }}>Present</option>
                                            <option value="Absent" {{ request('status') == 'Absent' ? 'selected' : '' }}>Absent</option>
                                            <option value="Late" {{ request('status') == 'Late' ? 'selected' : '' }}>Late</option>
                                            <option value="On Leave" {{ request('status') == 'On Leave' ? 'selected' : '' }}>On Leave</option>
                                            <option value="Half Day" {{ request('status') == 'Half Day' ? 'selected' : '' }}>Half Day</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex">
                                        <button type="submit" class="btn btn-primary btn-md me-2 flex-grow-1">
                                            <i class="bi bi-funnel me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center" title="Reset filters" style="min-width: 36px;">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                            <span>Reset</span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- End Filter Form -->

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
                                                    <img src="{{ $attendance->employee->user->profile_photo_url ?? asset('images/avatar.png') }}" 
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
                                                {{ \Carbon\Carbon::parse($attendance->check_in)->format('h:i:s A') }}
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
                                                {{ \Carbon\Carbon::parse($attendance->check_out)->format('h:i:s A') }}
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
                                                ($attendance->status === 'On Leave' ? 'info' : 
                                                ($attendance->status === 'Holiday' ? 'primary' : 'secondary')))) 
                                            }}">
                                                {{ $attendance->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->check_in && $attendance->check_out)
                                                @php
                                                    $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                                    $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                                    $hours = $checkIn->diffInHours($checkOut);
                                                    $minutes = $checkIn->diffInMinutes($checkOut) % 60;
                                                    // dd($checkIn, $checkOut);
                                                @endphp
                                                {{ sprintf('%d:%02d', $hours, $minutes) }} hrs
                                            @else
                                                <span class="text-muted">-:--</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                    class="btn btn-outline-primary edit-attendance" 
                                                    data-id="{{ $attendance->id }}"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top"
                                                    title="Edit Record"
                                                    aria-label="Edit">
                                                    <span class="btn-content">Edit
                                                        <i class="bi bi-pencil"></i>
                                                    </span>
                                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                </button>
                                                <button type="button" 
                                                    class="btn btn-outline-danger delete-attendance"
                                                    data-id="{{ $attendance->id }}"
                                                    data-employee="{{ $attendance->employee->user->name ?? 'N/A' }}"
                                                    data-date="{{ $attendance->date->format('M d, Y') }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Delete Record"
                                                    aria-label="Delete">
                                                    <span class="btn-content">Delete
                                                        <i class="bi bi-trash"></i>
                                                    </span>
                                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="alert alert-warning mb-0" role="alert">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                No attendance records found for the selected criteria.
                                                @if(request()->hasAny(['date_range', 'employee_id', 'department_id', 'status']))
                                                    <a href="{{ route('admin.attendance.index') }}" class="alert-link ms-2">Clear filters</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of {{ $attendances->total() }} entries
                        </div>
                        <div>
                            {{ $attendances->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Attendance Modal -->
<div class="modal fade" id="addAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addAttendanceForm" action="{{ route('admin.attendance.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Attendance Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                        <select class="form-select" id="employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->user->name }} ({{ $employee->employee_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_in" class="form-label">Check In</label>
                                <input type="time" class="form-control" id="check_in" name="check_in">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="check_out" class="form-label">Check Out</label>
                                <input type="time" class="form-control" id="check_out" name="check_out">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Half Day">Half Day</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editAttendanceForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_date" name="date" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_check_in" class="form-label">Check In</label>
                                <input type="time" class="form-control" id="edit_check_in" name="check_in">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_check_out" class="form-label">Check Out</label>
                                <input type="time" class="form-control" id="edit_check_out" name="check_out">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Half Day">Half Day</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="edit_remarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="importForm" action="{{ route('admin.attendance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Attendance Records</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(session('import_errors'))
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">Some records could not be imported:</h6>
                            <ul class="mb-0">
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Choose Excel File</label>
                        <input class="form-control" type="file" id="importFile" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            Please upload an Excel/CSV file with the correct format. 
                            <a href="{{ route('admin.attendance.template') }}" id="downloadTemplate">
                                <i class="bi bi-download"></i> Download template
                            </a>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="overwriteExisting" name="overwrite_existing" value="1">
                        <label class="form-check-label" for="overwriteExisting">
                            Overwrite existing records for the same employee and date
                        </label>
                    </div>
                    
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">Import Instructions</h6>
                        </div>
                        <div class="card-body small">
                            <ol class="mb-0">
                                <li>Use the template to ensure correct formatting</li>
                                <li>Required fields: <code>employee_id</code>, <code>date</code>, <code>status</code></li>
                                <li>Date format: YYYY-MM-DD</li>
                                <li>Time format: HH:MM:SS (24-hour format)</li>
                                <li>Valid status values: Present, Absent, Late, On Leave, Half Day</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Import Records
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="exportForm" action="{{ route('admin.attendance.export') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Export Attendance Records</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="exportType" class="form-label">Export Type</label>
                        <select class="form-select" id="exportType" name="type" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exportDateRange" class="form-label">Date Range</label>
                        <input type="text" class="form-control" id="exportDateRange" name="date_range" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Export Records
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .daterangepicker {
        z-index: 1060 !important;
    }
    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeeba;
        color: #856404;
    }
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
    .btn .spinner-border {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }
    .btn .btn-content {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.2s, visibility 0.2s;
    }
    .btn.loading .btn-content {
        visibility: hidden;
        opacity: 0;
    }
    .btn.loading .spinner-border {
        display: inline-block !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script> -->

<script>
$(document).ready(function() {
    // Initialize date range picker with validation
    $('.daterange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'YYYY-MM-DD',
            cancelLabel: 'Clear'
        },
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    // Set the initial value if it exists
    $('.daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Validate date range on form submit
    $('#filterForm').on('submit', function(e) {
        const dateRange = $('#date_range').val();
        if (dateRange) {
            const dates = dateRange.split(' - ');
            if (dates.length !== 2 || !moment(dates[0], 'YYYY-MM-DD', true).isValid() || !moment(dates[1], 'YYYY-MM-DD', true).isValid()) {
                e.preventDefault();
                showToast('Error', 'Please select a valid date range in the format YYYY-MM-DD', 'error');
                return false;
            }
            
            const startDate = moment(dates[0]);
            const endDate = moment(dates[1]);
            const diffDays = endDate.diff(startDate, 'days');
            
            if (diffDays < 0) {
                e.preventDefault();
                showToast('Error', 'End date cannot be before start date', 'error');
                return false;
            }
            
            if (diffDays > 365) {
                e.preventDefault();
                showToast('Error', 'Date range cannot be more than 1 year', 'error');
                return false;
            }
        }
    });

    // Handle export button clicks
    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        const exportType = $(this).data('type');
        let url = new URL('{{ route("admin.attendance.export") }}');
        
        // Get current filter values
        const params = new URLSearchParams(window.location.search);
        
        // Add type parameter
        params.set('type', exportType);
        
        // Update URL with all current filters
        url.search = params.toString();
        
        // Navigate to export URL
        window.location.href = url.toString();
    });
    
    // Show file name when file is selected
    $('#importFile').on('change', function() {
        const fileName = $(this).val().split('\\\\').pop();
        if (fileName) {
            $(this).next('.form-text').html(`Selected file: <strong>${fileName}</strong>`);
        }
    });
    
    // Handle form submission with loading state
    $('#importForm').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Importing...');
    });
    
    // Initialize date range picker
    // $('.daterange').daterangepicker({
    //     autoUpdateInput: false,
    //     locale: {
    //         format: 'YYYY-MM-DD',
    //         cancelLabel: 'Clear'
    //     },
    //     ranges: {
    //        'Today': [moment(), moment()],
    //        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    //        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    //        'This Month': [moment().startOf('month'), moment().endOf('month')],
    //        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    //     }
    // });

    $('.daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle edit attendance
    $(document).on('click', '.edit-attendance', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        
        // Show loading state
        $btn.prop('disabled', true).addClass('loading');
        
        // Fetch attendance data via AJAX
        $.ajax({
            url: `/admin/attendance/${id}/edit`,
            type: 'GET',
            success: function(response) {
                console.log(response);
                // Populate form fields
                $('#edit_id').val(response.id);
                $('#edit_date').val(response.date);
                $('#edit_check_in').val(response.check_in ? response.check_in.substring(0, 5) : '');
                $('#edit_check_out').val(response.check_out ? response.check_out.substring(0, 5) : '');
                $('#edit_status').val(response.status);
                $('#edit_remarks').val(response.remarks);
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
                modal.show();
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while loading the attendance record.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast('Error', errorMessage, 'danger');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('loading');
            }
        });
    });

    // Handle form submission for editing attendance
    $('#editAttendanceForm').submit(function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const id = $('#edit_id').val();
        
        // Show loading state
        $submitBtn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
        );
        
        // Submit form data
        $.ajax({
            url: `/admin/attendance/${id}`,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                showToast('Success', 'Attendance record updated successfully!', 'success');
                // Close the modal and refresh the table
                const modal = bootstrap.Modal.getInstance(document.getElementById('editAttendanceModal'));
                modal.hide();
                window.location.reload();
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while updating the attendance record.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                    
                    // Handle validation errors
                    if (xhr.responseJSON.errors) {
                        const errors = [];
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors.push(value[0]);
                        });
                        errorMessage = errors.join('<br>');
                    }
                }
                showToast('Error', errorMessage, 'danger');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('Save Changes');
            }
        });
    });

    // Handle delete attendance
    $(document).on('click', '.delete-attendance', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const id = $btn.data('id');
        const employeeName = $btn.data('employee');
        const date = $btn.data('date');
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Delete Attendance Record',
            html: `Are you sure you want to delete the attendance record for <strong>${employeeName}</strong> on <strong>${date}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise((resolve) => {
                    // Show loading state on button
                    $btn.prop('disabled', true).addClass('loading');
                    
                    // Send delete request
                    $.ajax({
                        url: `{{ route('admin.attendance.destroy', ':id') }}`.replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            resolve(true);
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while deleting the attendance record.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.showValidationMessage(errorMessage);
                            resolve(false);
                        },
                        complete: function() {
                            $btn.prop('disabled', false).removeClass('loading');
                        }
                    });
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The attendance record has been deleted.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    willClose: () => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    }
                });
            }
        });
    });
    
    // Show toast notification
    function showToast(title, message, type) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        Toast.fire({
            icon: type,
            title: title,
            html: message
        });
    }
});
</script>
@endpush
