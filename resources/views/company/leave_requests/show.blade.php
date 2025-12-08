@extends('layouts.app')

@section('title', 'View Leave Request')

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>View Leave Request</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('leaves.leave-requests.index') }}">Leave Requests</a></div>
            <div class="breadcrumb-item active"><a href="">View</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card glass-card">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Leave Request Details</h4>
    </div>

    <div class="card-body">
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-md-6">
                <h6 class="glass-section-title"><i class="fas fa-user-circle me-2"></i>Employee Info</h6>

                <p><span class="label">Employee Name:</span> <span class="value">{{ $leaveRequest->employee->name }}</span></p>
                <p><span class="label">Department:</span> <span class="value">{{ $leaveRequest->employee->department->name ?? '-' }}</span></p>
                <p><span class="label">Leave Type:</span> <span class="value">{{ $leaveRequest->leaveType->name }}</span></p>
                <p><span class="label">Start Date:</span> <span class="value">{{ $leaveRequest->start_date->format('Y-m-d') }}</span></p>
                <p><span class="label">End Date:</span> <span class="value">{{ $leaveRequest->end_date->format('Y-m-d') }}</span></p>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <h6 class="glass-section-title"><i class="fas fa-clock me-2"></i>Leave Duration</h6>

                <p><span class="label">Total Days:</span>
                    <span class="value">{{ $leaveRequest->total_days }}
                    <span class="text-muted">({{ count($approvedWorkingDays) }} working days)</span></span>
                </p>

                <p class="label">Working Days ({{ count($approvedWorkingDays) }})</p>
                <p>
                    @forelse($approvedWorkingDays as $date)
                        <span class="badge badge-primary mb-1">{{ \Carbon\Carbon::parse($date)->format('M d, Y (D)') }}</span>
                    @empty
                        <span class="text-muted">No working days</span>
                    @endforelse
                </p>

                <p class="label">Weekend Days ({{ count($weekendDays) }})</p>
                <p>
                    @forelse($weekendDays as $date)
                        <span class="badge badge-secondary mb-1">{{ \Carbon\Carbon::parse($date)->format('M d, Y (D)') }}</span>
                    @empty
                        <span class="text-muted">No weekend days</span>
                    @endforelse
                </p>

                <p class="label">Holiday Days ({{ count($holidayDates) }})</p>
                <p>
                    @forelse($holidayDates as $date)
                        <span class="badge badge-success mb-1">{{ \Carbon\Carbon::parse($date)->format('M d, Y (D)') }}</span>
                    @empty
                        <span class="text-muted">No holidays</span>
                    @endforelse
                </p>

                <p><span class="label">Status:</span>
                    <span class="badge badge-{{ $leaveRequest->status_color }}">{{ ucfirst($leaveRequest->status) }}</span>
                </p>

                <p><span class="label">Reason:</span> <span class="value">{{ $leaveRequest->reason }}</span></p>

                @if($leaveRequest->attachment)
                    <div class="mt-3">
                        <a href="{{ Storage::url($leaveRequest->attachment) }}"
                           target="_blank" class="btn btn-sm btn-glass">
                            <i class="fas fa-download"></i> Download Attachment
                        </a>
                    </div>
                @endif

                @if($leaveRequest->status === 'rejected' && $leaveRequest->admin_remarks)
                    <div class="mt-3">
                        <p><span class="label">Rejection Reason:</span></p>
                        <p class="text-warning">{{ $leaveRequest->admin_remarks }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Buttons -->
        @if($leaveRequest->status === 'pending')
            <div class="row mt-4 text-center">
                <div class="col-md-6 mb-2">
                    <form action="{{ route('leaves.leave-requests.approve', $leaveRequest->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-glass text-success">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </form>
                </div>
                <div class="col-md-6 mb-2">
                    <button type="button" class="btn btn-glass text-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        @endif

        <div class="d-flex gap-3 justify-content-center mt-4">
             <a href="{{ route('leaves.leave-requests.index') }}"
              class="btn btn-secondary px-4 rounded-pill shadow-sm">
              <i class="bi bi-arrow-left me-2"></i>Back to Leave Requests
              </a>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>


@if($leaveRequest->status === 'pending')
    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('leaves.leave-requests.reject', $leaveRequest->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Leave Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejection_reason">Rejection Reason (Optional)</label>
                            <textarea name="rejection_reason"
                                      id="rejection_reason"
                                      class="form-control"
                                      rows="3">{{ old('rejection_reason') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </section>
@endif
@endsection
