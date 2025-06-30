@extends('layouts.app')

@section('title', 'View Leave Request')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>View Leave Request</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('company.leave-requests.index') }}">Leave Requests</a></div>
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
                                    <label>Employee Name</label>
                                    <p class="form-control-static">{{ $leaveRequest->employee->name }}</p>
                                </div>

                                <div class="form-group">
                                    <label>Department</label>
                                    <p class="form-control-static">{{ $leaveRequest->employee->department->name ?? '-' }}</p>
                                </div>

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
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Days</label>
                                    <p class="form-control-static">
                                        {{ $leaveRequest->total_days }} 
                                        <span class="text-muted">({{ count($approvedWorkingDays) }} working days)</span>
                                    </p>
                                </div>
                                
                                <div class="form-group">
                                    <label>Working Days ({{ count($approvedWorkingDays) }})</label>
                                    <p class="form-control-static">
                                        @if(count($approvedWorkingDays) > 0)
                                            @foreach($approvedWorkingDays as $date)
                                                <span class="badge badge-primary mr-1 mb-1">
                                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y (D)') }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No working days specified</span>
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
                                            <span class="text-muted">No weekend days in this period</span>
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
                                            <span class="text-muted">No holidays in this period</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label>Status</label>
                                    <p class="form-control-static">
                                        <span class="badge badge-{{ $leaveRequest->status_color }}">
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

                                @if($leaveRequest->status === 'rejected' && $leaveRequest->admin_remarks)
                                    <div class="form-group">
                                        <label>Rejection Reason</label>
                                        <p class="form-control-static">{{ $leaveRequest->admin_remarks }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($leaveRequest->status === 'pending')
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <form action="{{ route('company.leave-requests.approve', $leaveRequest->id) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> Approve Leave Request
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            data-toggle="modal" 
                                            data-target="#rejectModal">
                                        <i class="fas fa-times"></i> Reject Leave Request
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div class="row mt-4">
                            <div class="col-12">
                                <a href="{{ route('company.leave-requests.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Leave Requests
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if($leaveRequest->status === 'pending')
    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('company.leave-requests.reject', $leaveRequest->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Leave Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" 
                                      id="rejection_reason" 
                                      class="form-control @error('rejection_reason') is-invalid @enderror" 
                                      rows="3" 
                                      required>{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
