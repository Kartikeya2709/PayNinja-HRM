@extends('layouts.app')

@section('title', 'Manage Resignations')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Manage Resignations</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Resignations</a></div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 bg-primary">
                    <div class="card-icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Requests</h4>
                        </div>
                        <div class="card-body text-white">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mobile-space-01">
                <div class="card card-statistic-1 bg-warning">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Pending</h4>
                        </div>
                        <div class="card-body text-white">{{ $stats['pending'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mobile-space">
                <div class="card card-statistic-1 bg-success">
                    <div class="card-icon ">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Approved</h4>
                        </div>
                        <div class="card-body text-white">{{ $stats['approved'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mobile-space">
                <div class="card card-statistic-1 bg-info">
                    <div class="card-icon ">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>This Month</h4>
                        </div>
                        <div class="card-body text-white">{{ $stats['this_month'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Filter Resignations</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.resignations.index') }}" class="row g-3">
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="hr_approved" {{ request('status') == 'hr_approved' ? 'selected' : '' }}>HR Approved</option>
                            <option value="manager_approved" {{ request('status') == 'manager_approved' ? 'selected' : '' }}>Manager Approved</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="withdrawn" {{ request('status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <label for="resignation_type" class="form-label">Type</label>
                        <select name="resignation_type" id="resignation_type" class="form-control">
                            <option value="">All Types</option>
                            <option value="voluntary" {{ request('resignation_type') == 'voluntary' ? 'selected' : '' }}>Voluntary</option>
                            <option value="retirement" {{ request('resignation_type') == 'retirement' ? 'selected' : '' }}>Retirement</option>
                            <option value="contract_end" {{ request('resignation_type') == 'contract_end' ? 'selected' : '' }}>Contract End</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <label class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.resignations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resignations Table -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Resignation Requests</h5>
            </div>
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

                <div class="table-responsive">
                    <table class="table table-striped" id="resignationsTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Type</th>
                                <th>Resignation Date</th>
                                <th>Last Working Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resignations as $resignation)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($resignation->employee->profile_image)
                                                <img src="{{ asset('storage/' . $resignation->employee->profile_image) }}"
                                                     class="rounded-circle mr-3" width="40" height="40" alt="Profile">
                                            @else
                                                <div class="avatar avatar-sm mr-3">
                                                    <span class="avatar-title rounded-circle bg-primary">
                                                        {{ substr($resignation->employee->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-weight-bold">{{ $resignation->employee->name }}</div>
                                                <div class="text-muted">{{ $resignation->employee->employee_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $resignation->employee->department->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $resignation->resignation_type_label }}</span>
                                    </td>
                                    <td>{{ $resignation->resignation_date->format('M d, Y') }}</td>
                                    <td>{{ $resignation->last_working_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $resignation->status_color }}">
                                            {{ $resignation->status_label }}
                                        </span>
                                        @if($resignation->remaining_days !== null && $resignation->remaining_days > 0)
                                            <br><small class="text-muted">{{ ceil($resignation->remaining_days) }} days left</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.resignations.show', $resignation) }}"
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if(in_array($resignation->status, ['pending', 'hr_approved', 'manager_approved']))
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-success btn-sm"
                                                        onclick="approveResignation({{ $resignation->id }})">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="rejectResignation({{ $resignation->id }})">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                                            <h6>No resignation requests found</h6>
                                            <p class="text-muted">No resignation requests match your current filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $resignations->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#resignationsTable').DataTable({
        order: [[3, 'desc']],
        pageLength: 25,
        searching: false, // Disable search since we have filters
        paging: false, // Disable pagination since we use Laravel pagination
        info: false
    });
});

function approveResignation(resignationId) {
    Swal.fire({
        title: 'Approve Resignation',
        input: 'textarea',
        inputLabel: 'Approval Remarks (Optional)',
        inputPlaceholder: 'Enter your approval remarks...',
        inputAttributes: {
            'aria-label': 'Enter your approval remarks'
        },
        showCancelButton: true,
        confirmButtonText: 'Approve',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        preConfirm: (remarks) => {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/approve`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            if (remarks) {
                const remarksInput = document.createElement('input');
                remarksInput.type = 'hidden';
                remarksInput.name = 'remarks';
                remarksInput.value = remarks;
                form.appendChild(remarksInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function rejectResignation(resignationId) {
    Swal.fire({
        title: 'Reject Resignation',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Enter the reason for rejection...',
        inputValidator: (value) => {
            if (!value) {
                return 'Rejection reason is required!';
            }
        },
        inputAttributes: {
            'aria-label': 'Enter rejection reason'
        },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        preConfirm: (reason) => {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/resignations/${resignationId}/reject`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush