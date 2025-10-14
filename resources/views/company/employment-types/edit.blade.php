@extends('layouts.app')

@section('content')
<div class="section container">
    <div class="section-header">
        <h1>Edit Employment Type</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="#">Edit Employment Type</a></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header justify-content-center mb-2">
                    <h3 class="card-title">Edit Employment Type</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('company.employment-types.update', $employmentType) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Name<span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employmentType->name) }}" required>
                            <small class="form-text text-muted">Enter the employment type name (e.g., Full-Time, Part-Time, Contract)</small>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $employmentType->description) }}</textarea>
                            <small class="form-text text-muted">Optional description for the employment type</small>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $employmentType->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">Active</label>
                            </div>
                            <small class="form-text text-muted">Check to make this employment type active</small>
                        </div>

                        <div class="form-group mt-4 text-center">
                            <button type="submit" class="btn btn-primary">Update Employment Type</button>
                            <a href="{{ route('company.employment-types.index') }}" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection