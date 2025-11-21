@extends('layouts.app')

@section('title', 'Company Details')

@section('content')
<div class="main-content">
    <section class="section container">
        <div class="section-header">
            <h1>Company Details: {{ $company->name }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('superadmin.companies.index') }}">Companies</a></div>
                <div class="breadcrumb-item">Company Details</div>
            </div>
        </div>

        <!-- Count Cards Row -->
        <div class="row emp-card">
            <div class="col-lg-3 px-1">
                <div class="card card-statistic-1 card-str-1">

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Company Admins</h4>


                            <div class="card-body">
                                {{ $companyAdminsCount }}
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 px-1">
                <div class="card card-statistic-1 card-str-2">

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Admins</h4>

                            <div class="card-body">
                                {{ $adminsCount }}
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 px-1">
                <div class="card card-statistic-1 card-str-3">

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Employees</h4>

                            <div class="card-body">
                                {{ $employeesCount }}
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 px-1">
                <div class="card card-statistic-1 card-str-4">

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Departments</h4>

                            <div class="card-body">
                                {{ $departmentsCount }}
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4 emp-card">
            <div class="col-lg-3 px-1">
                <div class="card card-statistic-1 card-str-5">

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Designations</h4>

                            <div class="card-body">
                                {{ $designationsCount }}
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-id-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
  
<!-- Company Details Card -->
<div class="row mt-4">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Company Information</h4>
                <div>
                    @if($company->is_active)
                        <form action="{{ route('superadmin.companies.deactivate', $company->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to deactivate this company and all its users?')">
                                <i class="fas fa-ban"></i> Deactivate Company
                            </button>
                        </form>
                    @else
                        <form action="{{ route('superadmin.companies.activate', $company->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to activate this company and all its users?')">
                                <i class="fas fa-check"></i> Activate Company
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr>
                                <th width="200">Company Name</th>
                                <td>{{ $company->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $company->email }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $company->phone ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr>
                                <th width="200">Domain</th>
                                <td>{{ $company->domain ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $company->address ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Section -->
<div class="row mt-4">
    <!-- Company Admins Table -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Company Admins</h4>
                {{-- <a href="{{ route('superadmin.assigned-company-admins.index') }}" class="btn btn-primary">View All</a> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($companyAdmins as $admin)
                            <tr>
                                <td>{{ $admin->user->name }}</td>
                                <td>{{ $admin->user->email }}</td>
                                <td>{{ $admin->phone ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No company admins found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Admins Table -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Admins</h4>
                {{-- <a href="{{ route('company.admins.index', $company->id) }}" class="btn btn-primary">View All</a> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admins as $admin)
                            <tr>
                                <td>{{ $admin->user->name }}</td>
                                <td>{{ $admin->user->email }}</td>
                                <td>{{ $admin->phone ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No admins found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Employees</h4>
                {{-- <a href="{{ route('company.employees.index', $company->id) }}" class="btn btn-primary">View All</a> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                            <tr>
                                <td>{{ $employee->user->name }}</td>
                                <td>{{ $employee->user->email }}</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>{{ $employee->designation->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if($employee->is_active)
                                        <form action="{{ route('superadmin.companies.employees.deactivate', [$company->id, $employee->id]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to deactivate this employee?')">
                                                <i class="fas fa-ban"></i> Deactivate
                                            </button>
                                        </form>
                                    @else
                                        @if($company->is_active)
                                            <form action="{{ route('superadmin.companies.employees.activate', [$company->id, $employee->id]) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to activate this employee?')">
                                                    <i class="fas fa-check"></i> Activate
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No employees found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Departments Table -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Departments</h4>
                {{-- <a href="{{ route('company.departments.index', $company->id) }}" class="btn btn-primary">View All</a> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Total Employees</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $department)
                            <tr>
                                <td>{{ $department->name }}</td>
                                <td>{{ $department->description ?? 'N/A' }}</td>
                                <td>{{ $department->employees_count }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No departments found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Designations Table -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Designations</h4>
                {{-- <a href="{{ route('company.designations.index', $company->id) }}" class="btn btn-primary">View All</a> --}}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Total Employees</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($designations as $designation)
                            <tr>
                                <td>{{ $designation->name }}</td>
                                <td>{{ $designation->description ?? 'N/A' }}</td>
                                <td>{{ $designation->employees_count }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No designations found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</section>
</div>

@push('style')
<style>
.card-icon {
    font-size: 2rem;
    opacity: 0.3;
    position: absolute;
    right: 35px;
    top: 50%;
    transform: translateY(-50%);
}

.card.card-statistic-1 .card-header h4 {
    font-size: 0.9rem;
}

.card.card-statistic-1 .card-body {
    font-size: 1.5rem;
    font-weight: 600;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
}
</style>
@endpush
@endsection