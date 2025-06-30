@extends('layouts.app')

@section('title', 'Request Leave')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Request Leave</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('employee.leave-requests.index') }}">My Leave Requests</a></div>
            <div class="breadcrumb-item active">Request Leave</div>
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
                        <h4>Leave Request Form</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('employee.leave-requests.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                                <select name="leave_type_id" 
                                        id="leave_type_id" 
                                        class="form-control select2 @error('leave_type_id') is-invalid @enderror" 
                                        required>
                                    <option value="">Select Leave Type</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" 
                                                data-requires-attachment="{{ $type->requires_attachment }}"
                                                {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} ({{ $leaveBalances->where('leave_type_id', $type->id)->first()->remaining_days ?? 0 }} days remaining)
                                        </option>
                                    @endforeach
                                </select>
                                @error('leave_type_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       name="start_date" 
                                       id="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date') }}" 
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
                                       value="{{ old('end_date') }}" 
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
                                          required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group" id="attachmentGroup" style="display: none;">
                                <label for="attachment">Attachment 
                                    <span class="text-danger">*</span>
                                    <small class="text-muted">(Required for this leave type)</small>
                                </label>
                                <input type="file" 
                                       name="attachment" 
                                       id="attachment" 
                                       class="form-control @error('attachment') is-invalid @enderror"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                @error('attachment')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Submit Leave Request</button>
                                <a href="{{ route('employee.leave-requests.index') }}" class="btn btn-link btn-danger">Cancel</a>
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
    $('.select2').select2();

    // Show/hide attachment field based on leave type
    $('#leave_type_id').change(function() {
        var requiresAttachment = $(this).find(':selected').data('requires-attachment');
        var attachmentGroup = $('#attachmentGroup');
        
        if (requiresAttachment) {
            attachmentGroup.show();
            $('#attachment').prop('required', true);
        } else {
            attachmentGroup.hide();
            $('#attachment').prop('required', false);
        }
    });

    // Trigger change event on page load if a leave type is selected
    if ($('#leave_type_id').val()) {
        $('#leave_type_id').trigger('change');
    }

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
