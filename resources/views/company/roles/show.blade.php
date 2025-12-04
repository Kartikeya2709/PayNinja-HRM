@extends('layouts.app')

@section('title', 'Role Details')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Role Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('company.roles.index') }}">Role Management</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4>{{ $role->name }}</h4>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('company.roles.edit', $role->id) }}" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Edit Role
                                    </a>
                                    <a href="{{ route('company.roles.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="form-label font-weight-bold">Role Name</label>
                                        <p class="form-control-static">{{ $role->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="form-label font-weight-bold">Status</label>
                                        <p class="form-control-static">
                                            @if($role->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="form-label font-weight-bold">Created Date</label>
                                        <p class="form-control-static">{{ $role->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="form-label font-weight-bold">Updated Date</label>
                                        <p class="form-control-static">{{ $role->updated_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label font-weight-bold">Permissions</label>
                                <div class="card">
                                    <div class="card-body">
                                        @php
                                            $permissions = json_decode($role->permissions ?? '{}', true);
                                        @endphp
                                        @if(!empty($permissions))
                                            <div class="row">
                                                @foreach($permissions as $permission => $hasAccess)
                                                    @if($hasAccess)
                                                        <div class="col-md-4 col-sm-6 mb-2">
                                                            <span class="badge bg-success me-1">
                                                                <i class="fas fa-check"></i>
                                                            </span>
                                                            {{ ucwords(str_replace('_', ' ', $permission)) }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted">No permissions assigned to this role.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection