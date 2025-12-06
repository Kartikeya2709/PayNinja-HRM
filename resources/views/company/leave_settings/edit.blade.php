@extends('layouts.app')

@section('title', 'Edit Leave Settings')

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>Edit Leave Settings for {{ $leaveType->name }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('company.company.leave-settings.index') }}">Leave Settings</a></div>
            <div class="breadcrumb-item active"><a href="">Edit</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('company.company.leave-settings.update', $leaveType->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Monthly and Yearly Limits Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Leave Limits</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="monthly_limit">Monthly Limit</label>
                                                <input type="number"
                                                       name="monthly_limit"
                                                       id="monthly_limit"
                                                       class="form-control @error('monthly_limit') is-invalid @enderror"
                                                       value="{{ old('monthly_limit', $leaveType->monthly_limit) }}"
                                                       min="0"
                                                       placeholder="Leave empty for unlimited">
                                                <small class="form-text text-muted">Maximum leaves allowed per month. Leave empty for unlimited.</small>
                                                @error('monthly_limit')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                            <!-- Disbursement Cycle Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Disbursement Cycle</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="disbursement_cycle">Disbursement Cycle *</label>
                                                <select name="disbursement_cycle"
                                                        id="disbursement_cycle"
                                                        class="form-control @error('disbursement_cycle') is-invalid @enderror"
                                                        required>
                                                    <option value="monthly" {{ old('disbursement_cycle', $leaveType->disbursement_cycle) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                    <option value="quarterly" {{ old('disbursement_cycle', $leaveType->disbursement_cycle) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                    <option value="half_yearly" {{ old('disbursement_cycle', $leaveType->disbursement_cycle) == 'half_yearly' ? 'selected' : '' }}>Half Yearly</option>
                                                    <option value="yearly" {{ old('disbursement_cycle', $leaveType->disbursement_cycle) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                                </select>
                                                @error('disbursement_cycle')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="disbursement_time">Disbursement Time *</label>
                                                <select name="disbursement_time"
                                                        id="disbursement_time"
                                                        class="form-control @error('disbursement_time') is-invalid @enderror"
                                                        required>
                                                    <option value="start_of_cycle" {{ old('disbursement_time', $leaveType->disbursement_time) == 'start_of_cycle' ? 'selected' : '' }}>Start of the cycle</option>
                                                    <option value="end_of_cycle" {{ old('disbursement_time', $leaveType->disbursement_time) == 'end_of_cycle' ? 'selected' : '' }}>End of the cycle</option>
                                                </select>
                                                @error('disbursement_time')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
             <!-- Leave Value Per Cycle Section -->
             <div class="card mb-4">
                 <div class="card-header">
                     <h5>Leave Value Per Cycle</h5>
                 </div>
                 <div class="card-body">
                     <div class="row">
                         <div class="col-md-6">
                             <div class="form-group mb-4">
                                 <label for="leave_value_per_cycle">Leave Value Per Cycle</label>
                                 <input type="number"
                                        name="leave_value_per_cycle"
                                        id="leave_value_per_cycle"
                                        class="form-control @error('leave_value_per_cycle') is-invalid @enderror"
                                        value="{{ old('leave_value_per_cycle', $leaveType->leave_value_per_cycle ?? 0) }}"
                                        min="0"
                                        step="0.01">
                                 <small class="form-text text-muted">Leave value to be used for each disbursement cycle. If not set, default_days will be used.</small>
                                 @error('leave_value_per_cycle')
                                     <div class="invalid-feedback">
                                         {{ $message }}
                                     </div>
                                 @enderror
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
                                            <div class="form-group mb-4">
                                                <label for="yearly_limit">Yearly Limit</label>
                                                <input type="number"
                                                       name="yearly_limit"
                                                       id="yearly_limit"
                                                       class="form-control @error('yearly_limit') is-invalid @enderror"
                                                       value="{{ old('yearly_limit', $leaveType->yearly_limit) }}"
                                                       min="0"
                                                       placeholder="Leave empty for unlimited">
                                                <small class="form-text text-muted">Maximum leaves allowed per year. Leave empty for unlimited.</small>
                                                @error('yearly_limit')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Carry Forward Settings Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Carry Forward Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   name="enable_carry_forward"
                                                   class="custom-control-input"
                                                   id="enable_carry_forward"
                                                   value="1"
                                                   {{ old('enable_carry_forward', $leaveType->enable_carry_forward) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="enable_carry_forward">
                                                Enable Carry Forward Settings
                                            </label>
                                        </div>
                                    </div>

                                    <div id="carryForwardSettings" style="{{ $leaveType->enable_carry_forward ? '' : 'display: none;' }}">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-4">
                                                    <label for="max_carry_forward_days">Max Carry Forward Days</label>
                                                    <input type="number"
                                                           name="max_carry_forward_days"
                                                           id="max_carry_forward_days"
                                                           class="form-control @error('max_carry_forward_days') is-invalid @enderror"
                                                           value="{{ old('max_carry_forward_days', $leaveType->max_carry_forward_days) }}"
                                                           min="0"
                                                           placeholder="Leave empty for unlimited">
                                                    <small class="form-text text-muted">Maximum days that can be carried forward. Leave empty for unlimited.</small>
                                                    @error('max_carry_forward_days')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-4">
                                                    <label for="yearly_carry_forward_limit">Yearly Carry Forward Limit</label>
                                                    <input type="number"
                                                           name="yearly_carry_forward_limit"
                                                           id="yearly_carry_forward_limit"
                                                           class="form-control @error('yearly_carry_forward_limit') is-invalid @enderror"
                                                           value="{{ old('yearly_carry_forward_limit', $leaveType->yearly_carry_forward_limit) }}"
                                                           min="0">
                                                    <small class="form-text text-muted">Maximum yearly carry forward leave limit for employees (in days).</small>
                                                    @error('yearly_carry_forward_limit')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       name="allow_carry_forward_to_next_year"
                                                       class="custom-control-input"
                                                       id="allow_carry_forward_to_next_year"
                                                       value="1"
                                                       {{ old('allow_carry_forward_to_next_year', $leaveType->allow_carry_forward_to_next_year) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="allow_carry_forward_to_next_year">
                                                    Allow carry forward leave balance to next year
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Half Day Settings Section -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Half Day Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="allow_half_day_leave">Allow Half Day Leave</label>
                                                <select name="allow_half_day_leave"
                                                        id="allow_half_day_leave"
                                                        class="form-control @error('allow_half_day_leave') is-invalid @enderror">
                                                    <option value="1" {{ old('allow_half_day_leave', $leaveType->allow_half_day_leave) ? 'selected' : '' }}>Yes</option>
                                                    <option value="0" {{ !old('allow_half_day_leave', $leaveType->allow_half_day_leave) ? 'selected' : '' }}>No</option>
                                                </select>
                                                <small class="form-text text-muted">Allow employees to apply for half day leave.</small>
                                                @error('allow_half_day_leave')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label for="allow_negative_balance">Allow Negative Balance</label>
                                                <select name="allow_negative_balance"
                                                        id="allow_negative_balance"
                                                        class="form-control @error('allow_negative_balance') is-invalid @enderror">
                                                    <option value="1" {{ old('allow_negative_balance', $leaveType->allow_negative_balance) ? 'selected' : '' }}>Yes</option>
                                                    <option value="0" {{ !old('allow_negative_balance', $leaveType->allow_negative_balance) ? 'selected' : '' }}>No</option>
                                                </select>
                                                <small class="form-text text-muted">Allow employees to have negative leave balance due to half day deductions.</small>
                                                @error('allow_negative_balance')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label for="half_day_deduction_priority">Half Day Deduction Priority *</label>
                                        <select name="half_day_deduction_priority"
                                                id="half_day_deduction_priority"
                                                class="form-control @error('half_day_deduction_priority') is-invalid @enderror"
                                                required>
                                            <option value="full_day_first" {{ old('half_day_deduction_priority', $leaveType->half_day_deduction_priority) == 'full_day_first' ? 'selected' : '' }}>Full day first</option>
                                            <option value="half_day_first" {{ old('half_day_deduction_priority', $leaveType->half_day_deduction_priority) == 'half_day_first' ? 'selected' : '' }}>Half day first</option>
                                        </select>
                                        <small class="form-text text-muted">Set priority for deduction due to half days.</small>
                                        @error('half_day_deduction_priority')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3 justify-content-center mt-4">
                                <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                <i class="bi bi-save me-2"></i>Update Leave Settings
                                </button>
                                <a href="{{ route('company.company.leave-settings.index') }}" class="btn btn-danger px-4 rounded-pill">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
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
    // Toggle carry forward settings visibility
    $('#enable_carry_forward').change(function() {
        if ($(this).is(':checked')) {
            $('#carryForwardSettings').show();
        } else {
            $('#carryForwardSettings').hide();
        }
    });
});
</script>
@endpush
