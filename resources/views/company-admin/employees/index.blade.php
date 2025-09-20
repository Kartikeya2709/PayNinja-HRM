@extends('layouts.app')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Employee Management</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Employee Management</a></div>
            </div>
        </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Employee Management</h5>
                    {{-- <a href="{{ route('company.employees.create', ['companyId' => auth()->user()->company_id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Employee
                    </a> --}}
                    <a href="{{ route('company-admin.employees.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center">

                        <i class="fas fa-plus me-1"></i> Create Employee
                    </a>
                </div>

                <!-- Filters and Search -->
                <div class="card-body mb-5">
                    <form id="filterForm" method="GET" action="{{ route('company-admin.employees.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="designation_id" class="form-label">Designation</label>
                                <select class="form-select" id="designation_id" name="designation_id">
                                    <option value="">All Designations</option>
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                            {{ $designation->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Search by name, email, or employee ID">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" id="resetFilters" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Employee</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Current Role</th>
                                    <th style="min-width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employeesTableBody">
                                @include('company-admin.employees._table')
                            </tbody>
                        </table>
                    </div>

                    <div id="paginationContainer" class="d-flex justify-content-center mt-3">
                        {{ $employees->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Single Role Change Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Role for <span id="modalEmployeeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roleChangeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleSelect" class="form-label">Select Role</label>
                        <select name="role" id="roleSelect" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                        </select>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var roleModal = document.getElementById('roleModal');
    var modalEmployeeName = document.getElementById('modalEmployeeName');
    var roleSelect = document.getElementById('roleSelect');
    var roleChangeForm = document.getElementById('roleChangeForm');

    var changeRoleButtons = document.querySelectorAll('.change-role-btn');
    changeRoleButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var employeeName = this.getAttribute('data-employee-name');
            var currentRole = this.getAttribute('data-current-role');
            var updateUrl = this.getAttribute('data-update-url');

            modalEmployeeName.textContent = employeeName;
            roleSelect.value = currentRole;
            roleChangeForm.action = updateUrl;
        });
    });

    // AJAX filtering functionality
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('search');
    const departmentSelect = document.getElementById('department_id');
    const designationSelect = document.getElementById('designation_id');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const employeesTableBody = document.getElementById('employeesTableBody');
    const paginationContainer = document.getElementById('paginationContainer');

    let filterTimeout;

    // Function to perform AJAX filtering
    function performFiltering() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (let [key, value] of formData.entries()) {
            if (value.trim() !== '') {
                params.append(key, value);
            }
        }

        const url = '{{ route("company-admin.employees.index") }}' + (params.toString() ? '?' + params.toString() : '');

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            employeesTableBody.innerHTML = data.html;
            paginationContainer.innerHTML = data.pagination;

            // Update URL
            window.history.replaceState({}, '', url);

            // Re-bind role change buttons after AJAX update
            bindRoleChangeButtons();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Function to bind role change buttons
    function bindRoleChangeButtons() {
        var changeRoleButtons = document.querySelectorAll('.change-role-btn');
        changeRoleButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var employeeName = this.getAttribute('data-employee-name');
                var currentRole = this.getAttribute('data-current-role');
                var updateUrl = this.getAttribute('data-update-url');

                modalEmployeeName.textContent = employeeName;
                roleSelect.value = currentRole;
                roleChangeForm.action = updateUrl;
            });
        });
    }

    // Search input with debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(performFiltering, 500);
    });

    // Department and designation select changes
    departmentSelect.addEventListener('change', performFiltering);
    designationSelect.addEventListener('change', performFiltering);

    // Reset filters
    resetFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        departmentSelect.value = '';
        designationSelect.value = '';

        // Update URL without query parameters
        window.history.replaceState({}, '', '{{ route("company-admin.employees.index") }}');

        performFiltering();
    });

    // Handle pagination links
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('.pagination a').href;

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                employeesTableBody.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;

                // Re-bind role change buttons after AJAX update
                bindRoleChangeButtons();

                // Update URL
                window.history.replaceState({}, '', url);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
});
</script>
@endsection
