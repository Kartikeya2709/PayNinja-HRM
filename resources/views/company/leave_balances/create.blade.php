@extends('layouts.app')

@section('title', 'Allocate Leave Balance')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Allocate Leave Balance</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('company.leave-balances.index') }}">Leave Balances</a></div>
            <div class="breadcrumb-item active">Allocate</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Individual Allocation</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('company.leave-balances.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" 
                                        id="employee_id" 
                                        class="form-control select2 @error('employee_id') is-invalid @enderror" 
                                        required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} - {{ $employee->department->name ?? 'No Department' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                                <select name="leave_type_id" 
                                        id="leave_type_id" 
                                        class="form-control select2 @error('leave_type_id') is-invalid @enderror" 
                                        required>
                                    <option value="">Select Leave Type</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" 
                                                data-default-days="{{ $type->default_days }}"
                                                {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
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
                                <label for="total_days">Total Days <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="total_days" 
                                       id="total_days" 
                                       class="form-control @error('total_days') is-invalid @enderror" 
                                       value="{{ old('total_days', 0) }}" 
                                       min="0" 
                                       required>
                                @error('total_days')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="year">Year <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="year" 
                                       id="year" 
                                       class="form-control @error('year') is-invalid @enderror" 
                                       value="{{ old('year', $currentYear) }}" 
                                       min="{{ $currentYear }}" 
                                       required>
                                @error('year')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Allocate Leave Balance</button>
                                <a href="{{ route('company.leave-balances.index') }}" class="btn btn-link">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Bulk Allocation</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('company.leave-balances.bulk-allocate') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label>Select Employees <span class="text-danger">*</span></label>
                                <select name="employee_ids[]" 
                                        class="form-control select2 @error('employee_ids') is-invalid @enderror" 
                                        multiple 
                                        required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ in_array($employee->id, old('employee_ids', [])) ? 'selected' : '' }}>
                                            {{ $employee->name }} - {{ $employee->department->name ?? 'No Department' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_ids')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="bulk_leave_type_id">Leave Type <span class="text-danger">*</span></label>
                                <select name="leave_type_id" 
                                        id="bulk_leave_type_id" 
                                        class="form-control select2 @error('leave_type_id') is-invalid @enderror" 
                                        required>
                                    <option value="">Select Leave Type</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" 
                                                data-default-days="{{ $type->default_days }}">
                                            {{ $type->name }}
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
                                <label for="bulk_total_days">Total Days <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="total_days" 
                                       id="bulk_total_days" 
                                       class="form-control @error('total_days') is-invalid @enderror" 
                                       value="{{ old('total_days', 0) }}" 
                                       min="0" 
                                       required>
                                @error('total_days')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="bulk_year">Year <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="year" 
                                       id="bulk_year" 
                                       class="form-control @error('year') is-invalid @enderror" 
                                       value="{{ old('year', $currentYear) }}" 
                                       min="{{ $currentYear }}" 
                                       required>
                                @error('year')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Bulk Allocate Leave Balance</button>
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

    // Individual allocation
    $('#leave_type_id').change(function() {
        var defaultDays = $(this).find(':selected').data('default-days');
        $('#total_days').val(defaultDays);
    });

    // Bulk allocation
    $('#bulk_leave_type_id').change(function() {
        var defaultDays = $(this).find(':selected').data('default-days');
        $('#bulk_total_days').val(defaultDays);
    });
});
</script>
@endpush
