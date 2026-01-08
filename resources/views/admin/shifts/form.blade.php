@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ isset($shift) ? 'Edit' : 'Create' }} Shift</h1>
        <a href="{{ route('admin.shifts.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Shifts
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ isset($shift) ? route('admin.shifts.update', Crypt::encrypt($shift->id)) : route('admin.shifts.store') }}" method="POST">
                @csrf
                @if(isset($shift))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Shift Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', $shift->name ?? '') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_id">Company *</label>
                            <select class="form-control @error('company_id') is-invalid @enderror"
                                    id="company_id" name="company_id" required {{ isset($shift) ? 'disabled' : '' }}>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ (old('company_id', $shift->company_id ?? '') == $company->id) ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(isset($shift))
                                <input type="hidden" name="company_id" value="{{ $shift->company_id }}">
                            @endif
                            @error('company_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_time">Start Time *</label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                   id="start_time" name="start_time"
                                   value="{{ old('start_time', isset($shift) ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '') }}"
                                   required>
                            @error('start_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_time">End Time *</label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                   id="end_time" name="end_time"
                                   value="{{ old('end_time', isset($shift) ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '') }}"
                                   required>
                            @error('end_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="grace_period_minutes">Grace Period (minutes) *</label>
                            <input type="number" class="form-control @error('grace_period_minutes') is-invalid @enderror"
                                   id="grace_period_minutes" name="grace_period_minutes" min="0" max="60"
                                   value="{{ old('grace_period_minutes', $shift->grace_period_minutes ?? 15) }}"
                                   required>
                            @error('grace_period_minutes')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox mt-4 pt-2">
                                <input type="checkbox" class="custom-control-input" id="is_night_shift"
                                       name="is_night_shift" value="1"
                                       {{ old('is_night_shift', $shift->is_night_shift ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_night_shift">
                                    Night Shift (spans midnight)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="has_break"
                                   name="has_break" value="1"
                                   {{ old('has_break', $shift->has_break ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="has_break">
                                Has Break Time
                            </label>
                        </div>
                    </div>
                </div>

                <div id="break-time-fields" style="display: {{ old('has_break', $shift->has_break ?? 0) ? 'block' : 'none' }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="break_start">Break Start Time</label>
                                <input type="time" class="form-control @error('break_start') is-invalid @enderror"
                                       id="break_start" name="break_start"
                                       value="{{ old('break_start', isset($shift) ? \Carbon\Carbon::parse($shift->break_start)->format('H:i') : '') }}">
                                @error('break_start')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="break_end">Break End Time</label>
                                <input type="time" class="form-control @error('break_end') is-invalid @enderror"
                                       id="break_end" name="break_end"
                                       value="{{ old('break_end', isset($shift) ? \Carbon\Carbon::parse($shift->break_end)->format('H:i') : '') }}">
                                @error('break_end')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $shift->description ?? '') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="is_default"
                                   name="is_default" value="1"
                                   {{ old('is_default', $shift->is_default ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_default">
                                Set as default shift for new employees
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($shift) ? 'Update' : 'Create' }} Shift
                        </button>
                        <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle break time fields
        $('#has_break').change(function() {
            if($(this).is(":checked")) {
                $('#break-time-fields').show();
                $('#break_start, #break_end').prop('required', true);
            } else {
                $('#break-time-fields').hide();
                $('#break_start, #break_end').prop('required', false);
            }
        });

        // Initialize the form
        if($('#has_break').is(":checked")) {
            $('#break-time-fields').show();
            $('#break_start, #break_end').prop('required', true);
        }

        // Validate end time is after start time
        $('form').on('submit', function(e) {
            const startTime = $('#start_time').val();
            const endTime = $('#end_time').val();

            if (startTime && endTime && startTime >= endTime && !$('#is_night_shift').is(':checked')) {
                e.preventDefault();
                alert('End time must be after start time for non-night shifts');
                return false;
            }

            if ($('#has_break').is(':checked')) {
                const breakStart = $('#break_start').val();
                const breakEnd = $('#break_end').val();

                if (breakStart && breakEnd && breakStart >= breakEnd) {
                    e.preventDefault();
                    alert('Break end time must be after break start time');
                    return false;
                }
            }
        });
    });
</script>
@endpush
