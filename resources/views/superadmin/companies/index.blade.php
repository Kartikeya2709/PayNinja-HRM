@extends('layouts.app')

@section('title', 'Manage Companies and Users')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Manage Companies and Users</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Companies & Users</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Companies</h2>
            <p class="section-lead mt-2">You can manage all companies and their admins here.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header card-margin">
                    <h4>All Companies</h4>
                    <div class="card-header-action">
                        <a href="{{ route('superadmin.companies.create') }}" class="btn button">Add New Company</a>
                    </div>
                </div>
                <div class="card-body Manage-Companies">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Company Name</th>
                                <th>Company Email</th>
                                <th>Domain</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($companies as $index => $company)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $company->name }}</td>
                                    <td>{{ $company->email }}</td>
                                    <td>{{ $company->domain ?? '-' }}</td>
                                    <td>{{ $company->phone ?? '-' }}</td>
                                    <td class="w-25">
                                        <a href="{{ route('superadmin.companies.show', $company->id) }}" class="btn btn-sm btn-success">View</a>
                                        <a href="{{ route('superadmin.companies.documents.index', $company->id) }}" class="btn btn-sm btn-primary">Documents</a>
                                        <a href="{{ route('superadmin.companies.edit', $company->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('superadmin.companies.destroy', $company->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No companies found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <h2 class="section-title mt-5">Admins & Super Admins</h2>
            <p class="section-lead mt-2">Below is the list of all Admins and Super Admins.</p>

            <div class="card">
                <div class="card-header">
                    <h4>All Admins & Super Admins</h4>
                </div>
                <div class="card-body Manage-Companies">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Company Name</th>
                                <th>User Email</th>
                                <th>Role</th>
                                <th>Actions</th> 
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                @if($user->role == 'admin' || $user->role == 'superadmin')
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->company ? $user->company->name : 'N/A' }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->role }}</td>
                                        <td>
                                            {{-- Add actions like edit/delete user if needed --}}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
