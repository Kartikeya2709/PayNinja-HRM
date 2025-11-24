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
            <di
            
             class="card">
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
                <div class="card-body mb-4">
                    <form id="filterForm" method="GET" action="{{ route('company-admin.employees.index') }}">
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-4">
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
                            <div class="col-lg-3 col-md-4">
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
                            <div class="col-lg-4 col-md-4">
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
                    <div id="statusAlert" class="alert d-none" role="alert"></div>

                     <!-- Alert message placeholder -->
                       <div id="messageBox"></div>


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
                            <option value="" selected disabled>Select a role</option>
                            @forelse($roles as $role)
                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                            @empty
                                <option value="">No roles available</option>
                            @endforelse
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



    <!-- Status Toggle Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="statusModalLabel">Update Employee Status</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="statusForm">
          <input type="hidden" id="employeeId">
          <div class="mb-3">
            <label class="form-label">Employee Name</label>
            <input type="text" id="employeeName" class="form-control" readonly>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="employeeActive">
            <label class="form-check-label" for="employeeActive">Active</label>
          </div>

          <div class="mb-3">
            <label class="form-label">Remark</label>
            <textarea id="employeeRemark" class="form-control" rows="3" placeholder="Enter remark..."></textarea>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saveStatusBtn" class="btn btn-primary">Save</button>
      </div>
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
            var currentRole = this.getAttribute('data-current-roleId');
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
                var currentRole = this.getAttribute('data-current-roleId');
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


<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    const employeeIdInput = document.getElementById('employeeId');
    const employeeNameInput = document.getElementById('employeeName');
    const employeeActiveInput = document.getElementById('employeeActive');
    const employeeRemarkInput = document.getElementById('employeeRemark');

    const messageBox = document.getElementById('messageBox'); // ✅ For showing messages

    // 🟩 Open modal when button clicked
    document.querySelectorAll('.toggle-status-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const status = this.dataset.status;

            employeeIdInput.value = id;
            employeeNameInput.value = name;
            employeeActiveInput.checked = (status === 'active');
            employeeRemarkInput.value = '';

            statusModal.show();
        });
    });

    // 🟦 Save Button: send AJAX to controller
    document.getElementById('saveStatusBtn').addEventListener('click', function () {
        const id = employeeIdInput.value;
        const isActive = employeeActiveInput.checked ? 1 : 0;
        const remark = employeeRemarkInput.value.trim();

        fetch(`/company-admin/employees/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                is_active: isActive,
                remark: remark
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // ✅ Update button UI
                const btn = document.querySelector(`.toggle-status-btn[data-id="${id}"]`);
                if (data.is_active) {
                    btn.classList.remove('btn-outline-danger');
                    btn.classList.add('btn-outline-success');
                    btn.innerHTML = `<i class="fas fa-check-circle me-1"></i>`;
                    btn.dataset.status = 'active';
                } else {
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-outline-danger');
                    btn.innerHTML = `<i class="fas fa-times-circle me-1"></i>`;
                    btn.dataset.status = 'inactive';
                }

                // ✅ Show success message on top
                messageBox.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;

                // Auto-hide after 3 seconds
                setTimeout(() => {
                    const alert = bootstrap.Alert.getOrCreateInstance(document.querySelector('.alert'));
                    if (alert) alert.close();
                }, 3000);

                statusModal.hide();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageBox.innerHTML = `
                <div class="alert alert-danger mt-3" role="alert">
                    Something went wrong. Please try again.
                </div>
            `;
        });
    });
});
</script>

@endsection
