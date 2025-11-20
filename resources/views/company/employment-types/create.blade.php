@extends('layouts.app')

@section('content')
<div class="section container">
    <div class="section-header">
        <h1>New Employment Type</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="#">New Employment Type</a></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header justify-content-center mb-2">
                    <h3 class="card-title">Create New Employment Type</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('employment-types.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Name<span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            <small class="form-text text-muted">Enter the employment type name (e.g., Full-Time, Part-Time, Contract)</small>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            <small class="form-text text-muted">Optional description for the employment type</small>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">Active</label>
                            </div>
                            <small class="form-text text-muted">Check to make this employment type active</small>
                        </div>

                      <div class="d-flex gap-3 justify-content-center mt-4">
                         <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                         <i class="bi bi-save me-2"></i>Create Employment Type
                         </button>
                         <a href="{{ route('employment-types.index') }}" class="btn btn-danger px-4 rounded-pill">
                         <i class="bi bi-x-circle me-2"></i>Cancel
                         </a>
                      </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection