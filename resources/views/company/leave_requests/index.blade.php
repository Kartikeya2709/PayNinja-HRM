@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
<div class="container">
<section class="section">
    <div class="section-header">
        <h1>Leave Requests</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Filter Leave Requests</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('company.leave-requests.index') }}" method="GET" class="row">
                            <div class="form-group col-md-3">
                                <label for="department_id">Department</label>
                                <select name="department_id" id="department_id" class="form-control select2">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="date_from">Date From</label>
                                <input type="date" 
                                       name="date_from" 
                                       id="date_from" 
                                       class="form-control" 
                                       value="{{ request('date_from') }}">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="date_to">Date To</label>
                                <input type="date" 
                                       name="date_to" 
                                       id="date_to" 
                                       class="form-control" 
                                       value="{{ request('date_to') }}">
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('company.leave-requests.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

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

                        <div class="table-responsive">
                            <table class="table table-striped" id="leaveRequestsTable">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaveRequests as $request)
                                        <tr>
                                            <td>{{ $request->employee->name }}</td>
                                            <td>{{ $request->employee->department->name ?? '-' }}</td>
                                            <td>{{ $request->leaveType->name }}</td>
                                            <td>{{ $request->start_date->format('Y-m-d') }}</td>
                                            <td>{{ $request->end_date->format('Y-m-d') }}</td>
                                            <td>{{ $request->working_days_count }}</td>
                                            <td>
                                                <span class="badge badge-{{ $request->status_color }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('company.leave-requests.show', $request->id) }}" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->status === 'pending')
                                                    <form action="{{ route('company.leave-requests.approve', $request->id) }}" 
                                                          method="POST" 
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('company.leave-requests.reject', $request->id) }}" 
                                                          method="POST" 
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Reject">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize select2
    $('.select2').select2();
    
    // Initialize DataTables
    var table = $('#leaveRequestsTable').DataTable({
        order: [[3, 'desc']],
        pageLength: 25,
        stateSave: true,
        stateDuration: 0
    });

    // Apply filters on form submit
    $('form').on('submit', function(e) {
        e.preventDefault();
        var url = $(this).attr('action') + '?' + $(this).serialize();
        window.location.href = url;
    });

    // Handle filter reset
    $('.btn-secondary').on('click', function(e) {
        e.preventDefault();
        window.location.href = $(this).attr('href');
    });
});
</script>
@endpush
