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

    .filter-badge {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        border-radius: 20px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .filter-actions .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 8px 16px;
    }

    .table-responsive {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        color: #495057;
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

    @media (max-width: 768px) {
        .filter-section .col-md-2,
        .filter-section .col-md-3 {
            margin-bottom: 15px;
        }

        .filter-actions {
            margin-top: 15px;
        }
    }

    /* Filter Toggle Styles */
    .filter-section.collapsed {
        display: none;
    }

    .filter-section {
        transition: all 0.3s ease-in-out;
        overflow: hidden;
    }

    #toggleFiltersBtn {
        transition: all 0.3s ease;
    }

    #toggleFiltersBtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    #toggleFiltersBtn .fa-filter {
        transition: transform 0.3s ease;
    }

    #toggleFiltersBtn.collapsed .fa-filter {
        transform: rotate(-90deg);
    }

    .filter-section.expanded {
        animation: slideDown 0.3s ease-out;
    }

    .filter-section.collapsed {
        animation: slideUp 0.3s ease-in;
    }

    @keyframes slideDown {
        from {
            max-height: 0;
            opacity: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
        to {
            max-height: 500px;
            opacity: 1;
            padding-top: 20px;
            padding-bottom: 20px;
        }
    }

    @keyframes slideUp {
        from {
            max-height: 500px;
            opacity: 1;
            padding-top: 20px;
            padding-bottom: 20px;
        }
        to {
            max-height: 0;
            opacity: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
    }
</style>
@endpush

@section('content')
    <div class="container">
        <section class="section">
            <div class="section-header">
                <h1>Regularization Requests</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="">Regularization Requests</a></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">


                    <div class="card">
                        <div class="card-1 card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Attendance Regularization Requests</h5>

                                </div>
                                <div class="btn-center d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="toggleFiltersBtn">
                                        <i class="fas fa-filter"></i> <span id="filterToggleText">Hide Filters</span>
                                    </button>
                                    @if (!is_null(Auth::user()->employee->reporting_manager_id))
                                        <a href="{{ route('regularization-requests.create') }}" class="btn btn-primary">New
                                            Request</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- Filter Section --}}
                        <div class="card-body border-bottom filter-section">
                            {{-- Active Filters Summary --}}
                            @php
                                $activeFilters = [];
                                if (!empty($filters['search'])) $activeFilters[] = 'Search: "' . $filters['search'] . '"';
                                if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
                                    $activeFilters[] = 'Date Range: ' . $filters['from_date'] . ' to ' . $filters['to_date'];
                                } elseif (!empty($filters['from_date'])) {
                                    $activeFilters[] = 'From: ' . $filters['from_date'];
                                } elseif (!empty($filters['to_date'])) {
                                    $activeFilters[] = 'To: ' . $filters['to_date'];
                                }
                                if (!empty($filters['month']) && !empty($filters['year'])) {
                                    $activeFilters[] = 'Month: ' . date('F', mktime(0, 0, 0, $filters['month'], 1)) . ' ' . $filters['year'];
                                } elseif (!empty($filters['month'])) {
                                    $activeFilters[] = 'Month: ' . date('F', mktime(0, 0, 0, $filters['month'], 1));
                                } elseif (!empty($filters['year'])) {
                                    $activeFilters[] = 'Year: ' . $filters['year'];
                                }
                                if (isset($employees) && !empty($filters['employee_id'])) {
                                    $selectedEmployee = $employees->find($filters['employee_id']);
                                    if ($selectedEmployee) {
                                        $activeFilters[] = 'Employee: ' . $selectedEmployee->name;
                                    }
                                }
                            @endphp



                            <form method="GET" action="{{ route('regularization.requests.index') }}" id="filterForm">
                                <div class="row g-3">
                                    {{-- Search Filter --}}
                                    <div class="col-md-3">
                                        <label for="search" class="form-label">Search</label>
                                        <input type="text" class="form-control" id="search" name="search"
                                               placeholder="Search by employee name, code, or reason"
                                               value="{{ $filters['search'] ?? '' }}">
                                    </div>

                                    {{-- Date Range Filters --}}
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

                                    {{-- Month and Year Filters --}}
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

                                    {{-- Employee Filter (for managers/admins) --}}
                                    @if(isset($employees))
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
                                    @endif

                                    {{-- Filter Actions --}}
                                    <div class="col-md-12">
                                        <div class="d-flex gap-2 filter-actions">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter"></i> Apply Filters
                                            </button>
                                            <a href="{{ route('regularization.requests.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Clear Filters
                                            </a>
                                            {{-- <button type="button" class="btn btn-outline-info" id="exportBtn">
                                                <i class="fas fa-download"></i> Export
                                            </button> --}}
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        @if (is_null(Auth::user()->employee->reporting_manager_id))
                            <form action="{{ route('regularization-requests.bulk-update') }}" method="POST"
                                id="bulk-action-form">
                                @csrf

                                <button type="button" id="bulk-approve-btn" class="btn btn-success">Approve Selected</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger">Reject Selected</button>
                    </div>
                </div>
                @endif

                @if (isset($pending_requests))
                    <ul class="nav nav-tabs Attendance-Regularization pt-4" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                                type="button" role="tab" aria-controls="pending" aria-selected="true">Pending</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved"
                                type="button" role="tab" aria-controls="approved"
                                aria-selected="false">Approved</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected"
                                type="button" role="tab" aria-controls="rejected"
                                aria-selected="false">Rejected</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            @include('employee.regularization._request_table', [
                                'requests' => $pending_requests,
                                'show_actions' => true,
                                'pagination_name' => 'pending_page',
                            ])
                        </div>
                        <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                            @include('employee.regularization._request_table', [
                                'requests' => $approved_requests,
                                'show_actions' => false,
                                'pagination_name' => 'approved_page',
                            ])
                        </div>
                        <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                            @include('employee.regularization._request_table', [
                                'requests' => $rejected_requests,
                                'show_actions' => false,
                                'pagination_name' => 'rejected_page',
                            ])
                        </div>
                    </div>
                @else
                    @include('employee.regularization._request_table', [
                        'requests' => $requests,
                        'show_actions' => false,
                    ])
                @endif

                @if (is_null(Auth::user()->employee->reporting_manager_id))
                    </form>
                @endif

                {{-- @if (isset($requests) && $requests instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ $requests->links() }}
                    @endif --}}
            </div>
        </section>
    </div>
    </div>
    </div>
    </div>

                <!-- Approval Modal -->
                <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="approvalModalLabel">Approve Requests</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="bulk_attendance_status">Attendance Status</label>
                                    <select name="attendance_status" id="bulk_attendance_status" class="form-control"
                                        required>
                                        <option value="">Select Status</option>
                                        <option value="Present">Present</option>
                                        <option value="Late">Late</option>
                                        <option value="Half Day">Half Day</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" id="confirm-approval-btn" class="btn btn-success">Confirm
                                    Approval</button>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter Toggle Functionality
            const toggleFiltersBtn = document.getElementById('toggleFiltersBtn');
            const filterSection = document.querySelector('.filter-section');
            const filterToggleText = document.getElementById('filterToggleText');

            // Load filter visibility state from localStorage
            const savedFilterState = localStorage.getItem('attendanceRegularizationFiltersVisible');
            const shouldShowFilters = savedFilterState !== 'false'; // Default to visible

            if (!shouldShowFilters) {
                filterSection.classList.add('collapsed');
                toggleFiltersBtn.classList.add('collapsed');
                filterToggleText.textContent = 'Show Filters';
                toggleFiltersBtn.innerHTML = '<i class="fas fa-filter"></i> <span id="filterToggleText">Show Filters</span>';
            }

            toggleFiltersBtn.addEventListener('click', function() {
                const isCurrentlyVisible = !filterSection.classList.contains('collapsed');

                if (isCurrentlyVisible) {
                    // Hide filters
                    filterSection.classList.remove('expanded');
                    filterSection.classList.add('collapsed');
                    toggleFiltersBtn.classList.add('collapsed');
                    filterToggleText.textContent = 'Show Filters';
                    toggleFiltersBtn.innerHTML = '<i class="fas fa-filter"></i> <span id="filterToggleText">Show Filters</span>';
                    localStorage.setItem('attendanceRegularizationFiltersVisible', 'false');
                } else {
                    // Show filters
                    filterSection.classList.remove('collapsed');
                    filterSection.classList.add('expanded');
                    toggleFiltersBtn.classList.remove('collapsed');
                    filterToggleText.textContent = 'Hide Filters';
                    toggleFiltersBtn.innerHTML = '<i class="fas fa-filter"></i> <span id="filterToggleText">Hide Filters</span>';
                    localStorage.setItem('attendanceRegularizationFiltersVisible', 'true');
                }
            });

            // Filter form auto-submit on change
            const filterForm = document.getElementById('filterForm');
            const filterInputs = filterForm.querySelectorAll('select, input[type="date"]');

            filterInputs.forEach(input => {
                input.addEventListener('change', function() {
                    filterForm.submit();
                });
            });

            // Search with debounce
            let searchTimeout;
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500);
                });
            }

            // Export functionality
            const exportBtn = document.getElementById('exportBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    const formData = new FormData(filterForm);
                    const params = new URLSearchParams();

                    for (let [key, value] of formData.entries()) {
                        if (value) params.append(key, value);
                    }

                    const url = '{{ route("regularization.requests.index") }}?' + params.toString() + '&export=csv';
                    window.open(url, '_blank');
                });
            }

            // Date validation
            const fromDate = document.getElementById('from_date');
            const toDate = document.getElementById('to_date');

            if (fromDate && toDate) {
                fromDate.addEventListener('change', function() {
                    if (toDate.value && this.value > toDate.value) {
                        toDate.value = this.value;
                    }
                });

                toDate.addEventListener('change', function() {
                    if (fromDate.value && this.value < fromDate.value) {
                        fromDate.value = this.value;
                    }
                });
            }

            // Month/Year dependency
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');

            if (monthSelect && yearSelect) {
                monthSelect.addEventListener('change', function() {
                    if (this.value && !yearSelect.value) {
                        yearSelect.value = new Date().getFullYear();
                    }
                });
            }

            @if (is_null(Auth::user()->employee->reporting_manager_id))
                // Select-all checkbox logic
                const selectAll = document.getElementById('select-all');
                if (selectAll) {
                    selectAll.addEventListener('click', function(event) {
                        let checkboxes = document.querySelectorAll('input[name="request_ids[]"]');
                        checkboxes.forEach(function(checkbox) {
                            checkbox.checked = event.target.checked;
                        });
                    });
                }

                const bulkApproveBtn = document.getElementById('bulk-approve-btn');
                const confirmApprovalBtn = document.getElementById('confirm-approval-btn');
                const bulkActionForm = document.getElementById('bulk-action-form');
                const approvalModal = new bootstrap.Modal(document.getElementById('approvalModal'));

                if (bulkApproveBtn) {
                    bulkApproveBtn.addEventListener('click', function() {
                        const selectedIds = Array.from(document.querySelectorAll(
                            'input[name="request_ids[]"]:checked')).map(cb => cb.value);
                        if (selectedIds.length === 0) {
                            alert('Please select at least one request to approve.');
                            return;
                        }
                        approvalModal.show();
                    });
                }

                if (confirmApprovalBtn) {
                    confirmApprovalBtn.addEventListener('click', function() {
                        const attendanceStatusSelect = document.getElementById('bulk_attendance_status');
                        if (attendanceStatusSelect.value === '') {
                            alert('Please select an attendance status.');
                            return;
                        }

                        // Add action and status to the form and submit
                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'approve';
                        bulkActionForm.appendChild(actionInput);

                        const statusInput = document.createElement('input');
                        statusInput.type = 'hidden';
                        statusInput.name = 'attendance_status';
                        statusInput.value = attendanceStatusSelect.value;
                        bulkActionForm.appendChild(statusInput);

                        bulkActionForm.submit();
                    });
                }
            @endif

            // Show active filters count
            function updateFilterCount() {
                const activeFilters = Array.from(filterForm.querySelectorAll('input[type="text"]:not([value=""]), select:not([value=""])'))
                    .filter(input => input.value !== '').length;

                const filterCount = document.getElementById('filter-count');
                if (filterCount) {
                    filterCount.textContent = activeFilters;
                    filterCount.style.display = activeFilters > 0 ? 'inline' : 'none';
                }
            }

            updateFilterCount();
            filterForm.addEventListener('change', updateFilterCount);
        });
    </script>
@endpush
