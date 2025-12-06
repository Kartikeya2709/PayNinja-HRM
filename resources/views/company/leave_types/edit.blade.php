@extends('layouts.app')

@section('title', 'Edit Leave Type')

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>Edit Leave Type</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('company.leave-types.index') }}">Leave Types</a></div>
            <div class="breadcrumb-item active"><a href="">Edit</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('company.leave-types.update', $leaveType->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group mb-4">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $leaveType->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="description">Description</label>
                                <textarea name="description"
                                          id="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="3">{{ old('description', $leaveType->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="default_days">Default Days <span class="text-danger">*</span></label>
                                <input type="number"
                                       name="default_days"
                                       id="default_days"
                                       class="form-control @error('default_days') is-invalid @enderror"
                                       value="{{ old('default_days', $leaveType->default_days) }}"
                                       min="0"
                                       required>
                                @error('default_days')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

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

                            <div class="form-group mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           name="requires_attachment"
                                           class="custom-control-input"
                                           id="requires_attachment"
                                           value="1"
                                           {{ old('requires_attachment', $leaveType->requires_attachment) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="requires_attachment">
                                        Requires Attachment
                                    </label>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           name="is_active"
                                           class="custom-control-input"
                                           id="is_active"
                                           value="1"
                                           {{ old('is_active', $leaveType->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>

                           <div class="d-flex gap-3 justify-content-center mt-4">
                              <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                              <i class="bi bi-save me-2"></i>Update Leave Type
                              </button>
                              <a href="{{ route('company.leave-types.index') }}" class="btn btn-danger px-4 rounded-pill">
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
