@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Employee Management</h5>
                    {{-- <a href="{{ route('company.employees.create', ['companyId' => auth()->user()->company_id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Employee
                    </a> --}}
                    <a href="{{ route('company-admin.employees.create') }}" class="btn btn-primary d-flex align-items-center">

                        <i class="fas fa-plus me-1"></i> Create Employee
                    </a>
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
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Current Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->user->name }}</td>
                                        <td>{{ $employee->user->email }}</td>
                                        <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ ucfirst($employee->user->role_name) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(auth()->user()->role === 'company_admin' || (auth()->user()->role === 'admin' && $employee->user->role !== 'company_admin'))
                                            <a href="{{ route('company.employees.edit', [$employee->company_id, $employee->id]) }}" class="btn btn-warning btn-sm me-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @endif
                                            @if($employee->user->role !== 'company_admin')
                                            <button type="button" class="btn btn-primary btn-sm change-role-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#roleModal"
                                                data-employee-id="{{ $employee->id }}"
                                                data-employee-name="{{ $employee->user->name }}"
                                                data-current-role="{{ $employee->user->role }}"
                                                data-update-url="{{ route('company-admin.employees.update-role', $employee->id) }}">
                                                <i class="fas fa-user-edit me-1"></i>Change Role
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
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
});
</script>
@endsection
