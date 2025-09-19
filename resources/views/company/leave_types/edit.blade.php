@extends('layouts.app')

@section('title', 'Edit Leave Type')

@section('content')
<section class="section">
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

                            <div class="form-group">
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

                            <div class="form-group">
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

                            <div class="form-group">
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

                            <div class="form-group">
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

                            <div class="form-group">
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

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Update Leave Type</button>
                                <a href="{{ route('company.leave-types.index') }}" class="btn btn-link">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
