@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('title', 'My Leave Requests')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>My Leave Requests</h1>
        <div class="section-header-button">
            <a href="{{ route('employee.leave-requests.create') }}" class="btn btn-primary">Request Leave</a>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Leave Balances</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Total Days</th>
                                        <th>Used Days</th>
                                        <th>Remaining Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaveBalances as $balance)
                                        <tr>
                                            <td>{{ $balance->leaveType->name }}</td>
                                            <td>{{ $balance->total_days }}</td>
                                            <td>{{ $balance->used_days }}</td>
                                            <td>
                                                <span class="badge badge-{{ $balance->remaining_days > 0 ? 'success' : 'danger' }}">
                                                    {{ $balance->remaining_days }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Leave Requests</h4>
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
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Working Days</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaveRequests as $request)
                                        <tr>
                                            <td>{{ $request->leaveType->name }}</td>
                                            <td>{{ $request->start_date->format('Y-m-d') }}</td>
                                            <td>{{ $request->end_date->format('Y-m-d') }}</td>
                                            <td>{{ $request->working_days_count }}</td>
                                            <td>
                                                <div class="text-center">
                                                    @if(is_array($request->working_days))
                                                        <span class="badge badge-info">{{ count($request->working_days) }} working days</span>
                                                        <div class="mt-1 small">
                                                            @foreach($request->working_days as $date)
                                                                <span class="badge badge-light text-dark mr-1">{{ \Carbon\Carbon::parse($date)->format('M d') }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="badge badge-info">0 working days</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $request->status_color }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('employee.leave-requests.show', $request->id) }}" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->status === 'pending')
                                                    <a href="{{ route('employee.leave-requests.edit', $request->id) }}" 
                                                       class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('employee.leave-requests.cancel', $request->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-sm">
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
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#leaveRequestsTable').DataTable({
        order: [[1, 'desc']],
        pageLength: 25
    });
});
</script>
@endpush
