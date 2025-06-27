@extends('layouts.app')

@section('title', 'View Leave Request')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>View Leave Request</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('employee.leave-requests.index') }}">My Leave Requests</a></div>
            <div class="breadcrumb-item active">View</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Leave Type</label>
                                    <p class="form-control-static">{{ $leaveRequest->leaveType->name }}</p>
                                </div>

                                <div class="form-group">
                                    <label>Start Date</label>
                                    <p class="form-control-static">{{ $leaveRequest->start_date->format('Y-m-d') }}</p>
                                </div>

                                <div class="form-group">
                                    <label>End Date</label>
                                    <p class="form-control-static">{{ $leaveRequest->end_date->format('Y-m-d') }}</p>
                                </div>

                                <div class="form-group">
                                    <label>Total Calendar Days</label>
                                    <p class="form-control-static">{{ $totalCalendarDays }} days</p>
                                </div>
                                
                                <div class="form-group">
                                    <label>Working Days ({{ count($workingDays) }})</label>
                                    <p class="form-control-static">
                                        @if(count($workingDays) > 0)
                                            @foreach($workingDays as $date)
                                                <span class="badge badge-primary mr-1 mb-1">
                                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y (D)') }}
                                                </span>
                                            @endforeach
                                        @else
                                            No working days in this period
                                        @endif
                                    </p>
                                </div>
                                
                                <div class="form-group">
                                    <label>Weekend Days ({{ count($weekendDays) }})</label>
                                    <p class="form-control-static">
                                        @if(count($weekendDays) > 0)
                                            @foreach($weekendDays as $date)
                                                <span class="badge badge-secondary mr-1 mb-1">
                                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y (D)') }}
                                                </span>
                                            @endforeach
                                        @else
                                            No weekend days in this period
                                        @endif
                                    </p>
                                </div>
                                
                                <div class="form-group">
                                    <label>Holiday Days ({{ count($holidayDates) }})</label>
                                    <p class="form-control-static">
                                        @if(count($holidayDates) > 0)
                                            @foreach($holidayDates as $date)
                                                <span class="badge badge-success mr-1 mb-1">
                                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y (D)') }}
                                                </span>
                                            @endforeach
                                        @else
                                            No holidays in this period
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <p class="form-control-static">
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'cancelled' => 'secondary',
                                                'in review' => 'info',
                                            ];
                                            $badgeColor = $statusColors[strtolower($leaveRequest->status)] ?? 'primary';
                                        @endphp
                                        <span class="badge badge-{{ $badgeColor }}">
                                            {{ ucfirst($leaveRequest->status) }}
                                        </span>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label>Reason</label>
                                    <p class="form-control-static">{{ $leaveRequest->reason }}</p>
                                </div>

                                @if($leaveRequest->attachment)
                                    <div class="form-group">
                                        <label>Attachment</label>
                                        <p class="form-control-static">
                                            <a href="{{ Storage::url($leaveRequest->attachment) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-download"></i> Download Attachment
                                            </a>
                                        </p>
                                    </div>
                                @endif

                                @if($leaveRequest->status === 'rejected' && $leaveRequest->rejection_reason)
                                    <div class="form-group">
                                        <label>Rejection Reason</label>
                                        <p class="form-control-static">{{ $leaveRequest->rejection_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if(isset($holidays) && $holidays->isNotEmpty())
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Holidays During Leave Period</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
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
                                                        to {{ $toDate->format('M d, Y') }}
                                                    @endif
                                                </td>
                                                <td>{{ $holiday->description ?? 'No description' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row mt-4">
                            <div class="col-12">
                                <a href="{{ route('employee.leave-requests.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Leave Requests
                                </a>
                                @if($leaveRequest->status === 'pending')
                                    <a href="{{ route('employee.leave-requests.edit', $leaveRequest->id) }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit Request
                                    </a>
                                    <form action="{{ route('employee.leave-requests.cancel', $leaveRequest->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Cancel Request
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
