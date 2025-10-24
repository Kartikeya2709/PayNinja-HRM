@extends('layouts.app')

@section('title', 'View Role')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>View Role</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('superadmin.roles.index') }}">Roles</a></div>
                <div class="breadcrumb-item">View</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Role Details</h2>
            <p class="section-lead mt-2">Detailed information about the role.</p>

            <div class="card">
                <div class="card-header">
                    <h4>Role Information</h4>
                    <div class="card-header-action">
                        <a href="{{ route('superadmin.roles.edit', $role->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Role
                        </a>
                        <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Role Name</label>
                                <p class="form-control-plaintext">{{ $role->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Company</label>
                                <p class="form-control-plaintext">{{ $role->company->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($role->is_active ? 'active' : 'inactive') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Is Default</label>
                                <p class="form-control-plaintext">
                                    <span class="badge {{ $role->is_default ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $role->is_default ? 'Yes' : 'No' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Created At</label>
                                <p class="form-control-plaintext">{{ $role->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Updated At</label>
                                <p class="form-control-plaintext">{{ $role->updated_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Permissions</label>
                                @if($role->permissions)
                                    <pre class="form-control-plaintext bg-light p-3 rounded">{{ str_replace('\\', '', json_encode(json_decode($role->permissions), JSON_PRETTY_PRINT)) }}</pre>
                                @else
                                    <p class="form-control-plaintext">No permissions set</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($role->users->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4>Assigned Users ({{ $role->users->count() }})</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Assigned At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($role->users as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->pivot->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection