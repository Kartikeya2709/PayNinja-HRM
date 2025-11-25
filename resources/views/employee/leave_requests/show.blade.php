@extends('layouts.app')

@section('title', 'View Leave Request')

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>View Leave Request</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('leave-requests.index') }}">My Leave Requests</a></div>
            <div class="breadcrumb-item active">View</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                
<div class="card">
    <div class="leave-header">
        <h5><i class="bi bi-calendar-event me-2"></i>Leave Request Summary</h5>
    </div>

    <div class="leave-body leave-req">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="bi bi-briefcase me-1"></i> Leave Type</label>
                    <p class="form-control-static">{{ $leaveRequest->leaveType->name }}</p>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-calendar-date me-1"></i> Start Date</label>
                    <p class="form-control-static">{{ $leaveRequest->start_date->format('Y-m-d') }}</p>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-briefcase-fill me-1"></i> Working Days ({{ count($workingDays) }})</label>
                    <p class="form-control-static">
                        @forelse($workingDays as $date)
                            <span class="badge">{{ \Carbon\Carbon::parse($date)->format('M d, Y (D)') }}</span>
                        @empty
                            No working days in this period
                        @endforelse
                    </p>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-chat-left-dots me-1"></i> Reason</label>
                    <p class="form-control-static">{{ $leaveRequest->reason }}</p>
                </div>

                
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="bi bi-hourglass-split me-1"></i> Status</label>
                    @php
                        $status = strtolower($leaveRequest->status);
                        $statusClass = match($status) {
                            'pending' => 'status-pending',
                            'approved' => 'status-approved',
                            'rejected' => 'status-rejected',
                            'cancelled' => 'status-cancelled',
                            'in review' => 'status-review',
                            default => 'badge-primary'
                        };
                    @endphp
                    <p class="form-control-static">
                        <span class="badge {{ $statusClass }}">{{ ucfirst($leaveRequest->status) }}</span>
                    </p>
                </div>
<div class="form-group">
                    <label><i class="bi bi-calendar-date-fill me-1"></i> End Date</label>
                    <p class="form-control-static">{{ $leaveRequest->end_date->format('Y-m-d') }}</p>
                </div>
                
<div class="form-group">
                    <label><i class="bi bi-clock me-1"></i> Total Calendar Days</label>
                    <p class="form-control-static">{{ $totalCalendarDays }} days</p>
                </div>
                @if($leaveRequest->attachment)
                <div class="form-group">
                    <label><i class="bi bi-paperclip me-1"></i> Attachment</label>
                    <p class="form-control-static">
                        <a href="{{ Storage::url($leaveRequest->attachment) }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    </p>
                </div>
                @endif

                @if($leaveRequest->status === 'rejected' && $leaveRequest->rejection_reason)
                <div class="form-group">
                    <label><i class="bi bi-exclamation-octagon me-1"></i> Rejection Reason</label>
                    <p class="form-control-static text-danger">{{ $leaveRequest->rejection_reason }}</p>
                </div>
                @endif
            </div>
        </div>

        @if(isset($holidays) && $holidays->isNotEmpty())
        <div class="mt-4">
            <h5 class="mb-3 text-info"><i class="bi bi-gift-fill me-2"></i> Holidays During Leave</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Holiday Name</th>
                            <th>Date Range</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holidays as $holiday)
                            @php
                                $fromDate = \Carbon\Carbon::parse($holiday->from_date);
                                $toDate = \Carbon\Carbon::parse($holiday->to_date);
                            @endphp
                            <tr>
                                <td>{{ $holiday->name }}</td>
                                <td>
                                    {{ $fromDate->format('M d, Y') }}
                                    @if(!$fromDate->isSameDay($toDate))
                                        – {{ $toDate->format('M d, Y') }}
                                    @endif
                                </td>
                                <td>{{ $holiday->description ?? 'No description' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="leave-actions">
            <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            @if($leaveRequest->status === 'pending')
                <a href="{{ route('leave-requests.edit', $leaveRequest->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('leave-requests.cancel', $leaveRequest->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this leave request?');">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
