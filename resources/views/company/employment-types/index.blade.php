@extends('layouts.app')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Employment Types</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Employment Types</a></div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Employment Types</h5>
                        <div class="card-tools">
                            <a href="{{ route('employment-types.create') }}" class="btn btn-primary">
                                Add New Employment Type
                            </a>
                        </div>
                    </div>
                    <div class="card-body employment-types-table">
                        @if(session('success'))
                            <div class="alert alert-success mb-2">
                                {{ session('success') }}
                            </div>
                        @endif

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employmentTypes as $employmentType)
                                    <tr>
                                        <td>{{ $employmentType->name }}</td>
                                        <td>{{ $employmentType->description ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $employmentType->is_active ? 'badge-success' : 'badge-danger' }}">
                                                {{ $employmentType->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('employment-types.edit', $employmentType) }}" class="btn btn-outline-info">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No employment types found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection