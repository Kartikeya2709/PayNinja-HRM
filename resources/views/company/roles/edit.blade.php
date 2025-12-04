@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Edit Role</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('company.roles.index') }}">Role Management</a></div>
                <div class="breadcrumb-item">Edit</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Edit Role: {{ $role->name }}</h2>
            <p class="section-lead mt-2">Modify the role details and permissions.</p>

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Role Information</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('company.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $role->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('is_active') is-invalid @enderror"
                                            id="is_active" name="is_active" required>
                                        <option value="1" {{ old('is_active', $role->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $role->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Permissions</label>
                                    <div class="permissions-tree">
                                        @php
                                            $selectedSlugs = old('permissions', json_decode($role->permissions ?? '{}', true) ?: []);
                                        @endphp
                                        @include('company.roles.partials.permissions-tree', [
                                            'slugs' => $slugs,
                                            'selectedSlugs' => $selectedSlugs,
                                            'level' => 0
                                        ])
                                    </div>
                                    <small class="form-text text-muted">Select the permissions for this role</small>
                                    @error('permissions')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Role
                            </button>
                            <a href="{{ route('company.roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection