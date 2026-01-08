@extends('layouts.app')

@push('styles')
<style>
    .filter-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .filter-section .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }

    .filter-section .form-control {
        border: 1px solid #ced4da;
        border-radius: 6px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .filter-section .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .table-responsive {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .nav-tabs .nav-link {
        border-radius: 8px 8px 0 0;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
</style>
@endpush

@section('content')
    <div class="container">
        <section class="section">
            <div class="section-header">
                <h1>Company Regularization Requests</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">Company Regularization</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attendance Regularization Requests</h5>
                </div>

                @if (session('success'))
                    <div class="alert alert-success m-3" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger m-3" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="card-body border-bottom filter-section">
                    @php
                        $listingRouteName = 'regularization-requests.company';
                    @endphp

                    <form method="GET" action="{{ route($listingRouteName) }}" id="companyFilterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       placeholder="Search by employee name, code, or reason"
                                       value="{{ $filters['search'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="from_date" name="from_date"
                                       value="{{ $filters['from_date'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="to_date" name="to_date"
                                       value="{{ $filters['to_date'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label for="month" class="form-label">Month</label>
                                <select class="form-control" id="month" name="month">
                                    <option value="">All Months</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ ($filters['month'] ?? '') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="year" class="form-label">Year</label>
                                <select class="form-control" id="year" name="year">
                                    <option value="">All Years</option>
                                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ ($filters['year'] ?? '') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="employee_id" class="form-label">Employee</label>
                                <select class="form-control" id="employee_id" name="employee_id">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ ($filters['employee_id'] ?? '') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }} ({{ $emp->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex gap-2 filter-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                    <a href="{{ route($listingRouteName) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear Filters
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <form action="{{ route('regularization-requests.bulk-update') }}" method="POST" id="company-bulk-action-form">
                    @csrf
                    <input type="hidden" name="action" id="company_bulk_action">
                    <div class="card-body d-flex gap-2">
                        <button type="button" id="company-bulk-approve-btn" class="btn btn-success">Approve Selected</button>
                        <button type="button" id="company-bulk-reject-btn" class="btn btn-danger">Reject Selected</button>
                    </div>

                    <div class="card-body">
                        <ul class="nav nav-tabs" id="companyRegularizationTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="company-pending-tab" data-bs-toggle="tab" data-bs-target="#company-pending"
                                    type="button" role="tab" aria-controls="company-pending" aria-selected="true">Pending</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="company-approved-tab" data-bs-toggle="tab" data-bs-target="#company-approved"
                                    type="button" role="tab" aria-controls="company-approved" aria-selected="false">Approved</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="company-rejected-tab" data-bs-toggle="tab" data-bs-target="#company-rejected"
                                    type="button" role="tab" aria-controls="company-rejected" aria-selected="false">Rejected</button>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="companyRegularizationContent">
                            <div class="tab-pane fade show active" id="company-pending" role="tabpanel" aria-labelledby="company-pending-tab">
                                @include('employee.regularization._request_table', [
                                    'requests' => $pending_requests,
                                    'show_actions' => true,
                                    'pagination_name' => 'pending_page',
                                    'canEditRegularization' => \App\Models\User::hasAccess('attendance-management/regularization-request-edit/{encryptedId}', true),
                                ])
                            </div>
                            <div class="tab-pane fade" id="company-approved" role="tabpanel" aria-labelledby="company-approved-tab">
                                @include('employee.regularization._request_table', [
                                    'requests' => $approved_requests,
                                    'show_actions' => false,
                                    'pagination_name' => 'approved_page',
                                ])
                            </div>
                            <div class="tab-pane fade" id="company-rejected" role="tabpanel" aria-labelledby="company-rejected-tab">
                                @include('employee.regularization._request_table', [
                                    'requests' => $rejected_requests,
                                    'show_actions' => false,
                                    'pagination_name' => 'rejected_page',
                                ])
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="modal fade" id="companyApprovalModal" tabindex="-1" aria-labelledby="companyApprovalModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="companyApprovalModalLabel">Approve Requests</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="company_bulk_attendance_status">Attendance Status</label>
                        <select name="attendance_status" id="company_bulk_attendance_status" class="form-control" form="company-bulk-action-form" required>
                            <option value="">Select Status</option>
                            <option value="Present">Present</option>
                            <option value="Late">Late</option>
                            <option value="Half Day">Half Day</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="company-confirm-approval-btn" class="btn btn-success">Confirm Approval</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bulkApproveBtn = document.getElementById('company-bulk-approve-btn');
        const confirmApprovalBtn = document.getElementById('company-confirm-approval-btn');
        const bulkRejectBtn = document.getElementById('company-bulk-reject-btn');
        const bulkActionForm = document.getElementById('company-bulk-action-form');
        const bulkActionInput = document.getElementById('company_bulk_action');
        const selectAllCheckbox = document.getElementById('select-all');

        // Select all functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="request_ids[]"]');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        }

        // Update select all checkbox when individual checkboxes change
        document.addEventListener('change', function(e) {
            if (e.target && e.target.name === 'request_ids[]') {
                const allCheckboxes = document.querySelectorAll('input[name="request_ids[]"]');
                const checkedCheckboxes = document.querySelectorAll('input[name="request_ids[]"]:checked');
                const selectAllCheckbox = document.getElementById('select-all');

                if (selectAllCheckbox) {
                    if (allCheckboxes.length === checkedCheckboxes.length && checkedCheckboxes.length > 0) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else if (checkedCheckboxes.length === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                }
            }
        });

        if (bulkApproveBtn) {
            bulkApproveBtn.addEventListener('click', function () {
                const modal = new bootstrap.Modal(document.getElementById('companyApprovalModal'));
                modal.show();
            });
        }

        if (confirmApprovalBtn) {
            confirmApprovalBtn.addEventListener('click', function () {
                const attendanceStatus = document.getElementById('company_bulk_attendance_status').value;
                if (!attendanceStatus) {
                    alert('Please select an attendance status.');
                    return;
                }
                bulkActionInput.value = 'approve';
                bulkActionForm.submit();
            });
        }

        if (bulkRejectBtn) {
            bulkRejectBtn.addEventListener('click', function () {
                bulkActionInput.value = 'reject';
                bulkActionForm.submit();
            });
        }
    });
</script>
@endpush
