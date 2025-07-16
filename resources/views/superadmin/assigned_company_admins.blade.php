@extends('layouts.app')

@section('title', 'Assigned Company Admins')

@section('content')
<div class="main-content-01">
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h1>Assigned Company Admins</h1>
            <a href="{{ route('superadmin.assign-company-admin.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Company Admin
            </a>
        </div>
        <div class="section-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover Assigned-Company">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Admin Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $index => $admin)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $admin->user->name ?? '-' }}</td>
                                <td>{{ $admin->user->email ?? '-' }}</td>
                                <td>{{ $admin->company->name ?? '-' }}</td>
                                <td>{{ $admin->phone ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('superadmin.assign-company-admin.edit', $admin->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No assigned company admins found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
