@extends('layouts.app')

@section('title', 'Leave Balances')

@section('content')
<div class="container">
<section class="section">
    <div class="section-header">
        <h1>Leave Balances</h1>
        <div class="section-header-button">
            <a href="{{ route('company.leave-balances.create') }}" class="btn btn-primary">Allocate Leave Balance</a>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible show fade">
                                <div class="alert-body">
                                    <button class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    {{ session('success') }}
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible show fade">
                                <div class="alert-body">
                                    <button class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    {{ session('error') }}
                                </div>
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="filter-container bg-light p-3 rounded">
                                    <form action="{{ route('company.leave-balances.index') }}" method="GET" id="filterForm" class="row align-items-end">
                                        <div class="col-md-3 mb-3">
                                            <label for="search">Search</label>
                                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name, email...">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="department">Department</label>
                                            <select class="form-control select2" id="department" name="department">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="leaveType">Leave Type</label>
                                            <select class="form-control select2" id="leaveType" name="leaveType">
                                                <option value="">All Leave Types</option>
                                                @foreach($leaveTypes as $type)
                                                    <option value="{{ $type->id }}" {{ request('leaveType') == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="year">Year</label>
                                            <select class="form-control" id="year" name="year">
                                                <option value="">All Years</option>
                                                @for($y = $currentYear + 1; $y >= $currentYear - 1; $y--)
                                                    <option value="{{ $y }}" {{ request('year', $currentYear) == $y ? 'selected' : '' }}>
                                                        {{ $y }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="balanceStatus">Balance Status</label>
                                            <select class="form-control" id="balanceStatus" name="balanceStatus">
                                                <option value="">All Status</option>
                                                <option value="available">Available</option>
                                                <option value="exhausted">Exhausted</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="balanceStatus">Balance Status</label>
                                            <select class="form-control" id="balanceStatus" name="balanceStatus">
                                                <option value="">All Balances</option>
                                                <option value="available" {{ request('balanceStatus') == 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="exhausted" {{ request('balanceStatus') == 'exhausted' ? 'selected' : '' }}>Exhausted</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1 mb-3">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-filter"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped" id="leaveBalancesTable">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Leave Type</th>
                                        <th>Total Days</th>
                                        <th>Used Days</th>
                                        <th>Remaining Days</th>
                                        <th>Year</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employees as $employee)
                                        <tr>
                                            <td>{{ $employee->name }}</td>
                                            <td>{{ $employee->department_name ?? '-' }}</td>
                                            <td>{{ $employee->leave_type_name }}</td>
                                            <td>{{ $employee->total_days }}</td>
                                            <td>{{ $employee->used_days }}</td>
                                            <td>
                                                @php
                                                    $remainingDays = $employee->total_days - $employee->used_days;
                                                    $badgeClass = $remainingDays > 0 ? 'success' : 'danger';
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }}">{{ $remainingDays }}</span>
                                            </td>
                                            <td>{{ $employee->year }}</td>
                                            <td>
                                                <a href="{{ route('company.leave-balances.edit', $employee->balance_id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No leave balances found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="btn-group">
                                    <button type="button" id="exportExcel" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-excel"></i> Export to Excel
                                    </button>
                                    <button type="button" id="exportPdf" class="btn btn-danger btn-sm ml-2">
                                        <i class="fas fa-file-pdf"></i> Export to PDF
                                    </button>
                                    <button type="button" id="resetFilters" class="btn btn-secondary btn-sm ml-2">
                                        <i class="fas fa-undo"></i> Reset Filters
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="float-right">
                                    {{ $employees->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.filter-container {
    border: 1px solid #e3e6f0;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();

    // Export functionality
    $('#exportExcel').click(function() {
        var params = new URLSearchParams(window.location.search);
        params.append('export', 'excel');
        window.location.href = '{{ route('company.leave-balances.index') }}?' + params.toString();
    });

    $('#exportPdf').click(function() {
        var params = new URLSearchParams(window.location.search);
        params.append('export', 'pdf');
        window.location.href = '{{ route('company.leave-balances.index') }}?' + params.toString();
    });

    // Reset filters
    $('#resetFilters').click(function() {
        window.location.href = '{{ route('company.leave-balances.index') }}';
    });
});
</script>
@endpush
