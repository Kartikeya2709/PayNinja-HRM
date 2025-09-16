@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Assign Shift: {{ $shift->name }}</h1>
        <a href="{{ route('admin.shifts.show', $shift) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Back to Shift
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Assign to Employees</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.shifts.assign', $shift) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="employee_ids">Select Employees *</label>
                    <select name="employee_ids[]" id="employee_ids" class="form-control select2 @error('employee_ids') is-invalid @enderror" multiple required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->user->name }} ({{ $employee->employee_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_ids')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Start Date *</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" 
                                   value="{{ old('start_date', now()->format('Y-m-d')) }}" 
                                   min="{{ now()->format('Y-m-d') }}" required>
                            @error('start_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">End Date (Optional)</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" 
                                   value="{{ old('end_date') }}" 
                                   min="{{ now()->addDay()->format('Y-m-d') }}">
                            <small class="form-text text-muted">Leave empty for ongoing assignment</small>
                            @error('end_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" value="1">
                        <label class="custom-control-label" for="is_default">
                            Set as default shift for selected employees
                        </label>
                        <small class="form-text text-muted">
                            This will unset any existing default shifts for these employees
                        </small>
                    </div>
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Assign Shift
                    </button>
                    <a href="{{ route('admin.shifts.show', $shift) }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        padding: 0.375rem 0.75rem;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #4e73df;
        border: 1px solid #4e73df;
        color: #fff;
        border-radius: 0.2rem;
        padding: 0 0.5rem;
        margin-top: 0.3rem;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 0.3rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Select employees',
            allowClear: true,
            width: '100%'
        });
        
        // Set minimum end date based on start date
        $('#start_date').on('change', function() {
            const startDate = new Date($(this).val());
            const minEndDate = new Date(startDate);
            minEndDate.setDate(minEndDate.getDate() + 1);
            
            $('#end_date').attr('min', minEndDate.toISOString().split('T')[0]);
            
            // If current end date is before new min date, clear it
            if ($('#end_date').val() && new Date($('#end_date').val()) < minEndDate) {
                $('#end_date').val('');
            }
        });
    });
</script>
@endpush
