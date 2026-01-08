@extends('layouts.app')

@section('title', 'Edit Leave Request')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Leave Request</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('leaves.my-leaves.leave-requests.index') }}">My Leave Requests</a></div>
            <div class="breadcrumb-item active">Edit Request</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Leave Request Form</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('leaves.my-leaves.leave-requests.update', \Illuminate\Support\Facades\Crypt::encrypt($leaveRequest->id)) }}"
                              method="POST"
                              enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                            <div class="form-group">
                                <label>Leave Type</label>
                                <p class="form-control-static">{{ $leaveRequest->leaveType->name }}</p>
                                <input type="hidden" name="leave_type_id" value="{{ $leaveRequest->leave_type_id }}">
                            </div>

                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       name="start_date"
                                       id="start_date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}"
                                       min="{{ now()->format('Y-m-d') }}"
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="end_date">End Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       name="end_date"
                                       id="end_date"
                                       class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date', $leaveRequest->end_date->format('Y-m-d')) }}"
                                       min="{{ now()->format('Y-m-d') }}"
                                       required>
                                @error('end_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="reason">Reason <span class="text-danger">*</span></label>
                                <textarea name="reason"
                                          id="reason"
                                          class="form-control @error('reason') is-invalid @enderror"
                                          rows="3"
                                          required>{{ old('reason', $leaveRequest->reason) }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            @if($leaveRequest->leaveType->requires_attachment)
                                <div class="form-group">
                                    <label for="attachment">Attachment
                                        @if(!$leaveRequest->attachment)
                                            <span class="text-danger">*</span>
                                        @endif
                                        <small class="text-muted">(Required for this leave type)</small>
                                    </label>
                                    @if($leaveRequest->attachment)
                                        <div class="mb-2">
                                            <a href="{{ Storage::url($leaveRequest->attachment) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-download"></i> Current Attachment
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file"
                                           name="attachment"
                                           id="attachment"
                                           class="form-control @error('attachment') is-invalid @enderror"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                           {{ !$leaveRequest->attachment ? 'required' : '' }}>
                                    @error('attachment')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            @endif

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Update Leave Request</button>
                                <a href="{{ route('leaves.my-leaves.leave-requests.index') }}" class="btn btn-link">Cancel</a>
                            </div>
                        </form>
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
    // Validate end date is after start date
    $('#start_date, #end_date').change(function() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if (startDate && endDate && startDate > endDate) {
            alert('End date must be after start date');
            $('#end_date').val('');
        }
    });
});
</script>
@endpush
