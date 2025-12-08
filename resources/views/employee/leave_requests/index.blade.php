@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('title', 'My Leave Requests')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Request Leave</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Request Leave</a></div>
            </div>
        </div>

        <div class="card">
            <div class="card-1">
                <h5 class="mb-0">My Leave Requests</h5>
                <div class="section-header-button">
                    <a href="{{ route('leaves.my-leaves.leave-requests.create') }}" class="btn btn-primary">Request Leave</a>
                </div>
            </div>
        </div>


        <div class="mt-4">
            <div class="col-12 px-1">
                <div class="card">
                    <div class="card-header btn-center justify-content-center">
                        <h5 class="mb-3">Leave Balances</h5>
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
                                            <span
                                                class="badge badge-{{ $balance->remaining_days > 0 ? 'success' : 'danger' }}">
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

                <div class="card mt-4">
                    <div class="card-header btn-center justify-content-center">
                        <h5 class="mb-3">Leave Requests</h5>
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
                                            <div class="text-left">
                                                @if(is_array($request->working_days))
                                                <span class="badge badge-info">{{ count($request->working_days) }}
                                                    working days</span>
                                                <div class="mt-1 small">
                                                    @foreach($request->working_days as $date)
                                                    <span
                                                        class="badge badge-light text-dark mr-1">{{ \Carbon\Carbon::parse($date)->format('M d') }}</span>
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
                                            <div class="btn-group btn-group-sm">
                                            <a href="{{ route('leaves.my-leaves.leave-requests.show', $request->id) }}"
                                                class="btn btn-outline-info action-btn" data-id="{{ $request->id }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Show Request"
                                                aria-label="Show">
                                                <span class="btn-content">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                    aria-hidden="true"></span>
                                            </a>

                                            @if($request->status === 'pending')
                                            <a href="{{ route('leaves.my-leaves.leave-requests.edit', $request->id) }}"
                                                class="btn btn-outline-warning edit-leave-request"
                                                data-id="{{ $request->id }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Edit Request" aria-label="Edit">
                                                <span class="btn-content">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                    aria-hidden="true"></span>
                                            </a>

                                            <form action="{{ route('leaves.my-leaves.leave-requests.cancel', $request->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                                @csrf
                                                <button type="submit"
                                                class="btn btn-outline-danger btn-sm action-btn rounded-start-0"
                                                data-id="1"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Delete Category"
                                                aria-label="Delete"
                                                onclick="return confirm('Are you sure you want to delete this category?')">
                                                <span class="btn-content">
                                                <i class="fas fa-trash"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                 </button>
                                            </form>
                                            </div>
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
        order: [
            [1, 'desc']
        ],
        pageLength: 25
    });
});
</script>
@endpush
