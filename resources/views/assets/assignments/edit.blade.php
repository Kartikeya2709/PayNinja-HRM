@extends('layouts.app')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-center">
                    <h3 class="card-title">Edit Asset Assignment</h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('assets.assignments.update', ['encryptedId' => Crypt::encrypt($assignment->id)]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4">
                                <div class="form-group">
                                    <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                    <select class="form-control @error('employee_id') is-invalid @enderror"
                                            id="employee_id" name="employee_id" required>
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ old('employee_id', $assignment->employee_id) == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->full_name ?? $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12 mb-4">
                                <div class="form-group">
                                    <label for="assigned_date">Assigned Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('assigned_date') is-invalid @enderror"
                                           id="assigned_date" name="assigned_date"
                                           value="{{ old('assigned_date', $assignment->assigned_date->format('Y-m-d')) }}" required>
                                    @error('assigned_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12 mb-4">
                                <div class="form-group">
                                    <label for="expected_return_date">Expected Return Date</label>
                                    <input type="date" class="form-control @error('expected_return_date') is-invalid @enderror"
                                           id="expected_return_date" name="expected_return_date"
                                           value="{{ old('expected_return_date', $assignment->expected_return_date? $assignment->expected_return_date->format('Y-m-d') : '') }}">
                                    @error('expected_return_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12 mb-4">
                                <div class="form-group">
                                    <label for="condition_on_assignment">Condition on Assignment <span class="text-danger">*</span></label>
                                    <select class="form-control @error('condition_on_assignment') is-invalid @enderror"
                                            id="condition_on_assignment" name="condition_on_assignment" required>
                                        <option value="">-- Select Condition --</option>
                                        <option value="good" {{ old('condition_on_assignment', $assignment->condition_on_assignment) == 'good' ? 'selected' : '' }}>Good</option>
                                        <option value="fair" {{ old('condition_on_assignment', $assignment->condition_on_assignment) == 'fair' ? 'selected' : '' }}>Fair</option>
                                        <option value="poor" {{ old('condition_on_assignment', $assignment->condition_on_assignment) == 'poor' ? 'selected' : '' }}>Poor</option>
                                        <option value="damaged" {{ old('condition_on_assignment', $assignment->condition_on_assignment) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                    </select>
                                    @error('condition_on_assignment')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mb-4">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="notes" name="notes" rows="3"
                                              placeholder="Optional notes...">{{ old('notes', $assignment->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('assets.assignments.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Assignment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
