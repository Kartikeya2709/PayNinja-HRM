@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Edit Role</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('superadmin.roles.index') }}">Roles</a></div>
                <div class="breadcrumb-item">Edit</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Edit Role</h2>
            <p class="section-lead mt-2">Update role information.</p>

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
                    <form action="{{ route('superadmin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
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
                            {{-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                                    <select class="form-control @error('company_id') is-invalid @enderror"
                                            id="company_id" name="company_id" required>
                                        <option value="">Select Company</option>
                                        @foreach(\App\Models\Company::all() as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $role->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row"> --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status', $role->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $role->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_default" class="form-label">Is Default Role</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="is_default" name="is_default" value="1"
                                               {{ old('is_default', $role->is_default) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">
                                            Mark as default role for the company
                                        </label>
                                    </div>
                                    @error('is_default')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Permissions</label>
                                    <div class="permissions-tree">
                                        @php
                                            $selectedSlugs = old('permissions', json_decode($role->permissions, true) ?? []);
                                        @endphp
                                        @include('superadmin.roles.partials.permissions-tree', [
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
                            <button type="submit" class="btn btn-primary">Update Role</button>
                            <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection