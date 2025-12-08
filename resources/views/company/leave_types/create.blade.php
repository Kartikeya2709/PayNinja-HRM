@extends('layouts.app')

@section('title', 'Create Leave Type')

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>Create Leave Type</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('leaves.leave-types.index') }}">Leave Types</a></div>
            <div class="breadcrumb-item active"><a href="">Create</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('leaves.leave-types.store') }}" method="POST">
                        @csrf

                            <div class="form-group mb-4">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
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
                                          rows="3">{{ old('description') }}</textarea>
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
                                       value="{{ old('default_days', 0) }}"
                                       min="0"
                                       required>
                                @error('default_days')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           name="requires_attachment"
                                           class="custom-control-input"
                                           id="requires_attachment"
                                           value="1"
                                           {{ old('requires_attachment') ? 'checked' : '' }}>
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
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>

                           <div class="d-flex gap-3 justify-content-center mt-4">
                              <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                              <i class="bi bi-save me-2"></i>Create Leave Type
                              </button>
                              <a href="{{ route('leaves.leave-types.index') }}" class="btn btn-danger px-4 rounded-pill">
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
