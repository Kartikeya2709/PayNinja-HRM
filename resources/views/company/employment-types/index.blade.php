@extends('layouts.app')

@section('title', 'Employment Types')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Employment Types</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Employment Types</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Employment Types</h3>
                            <div class="card-tools">
                                @if(\App\Models\User::hasAccess('employment-type-create', true))
                                <a href="{{ route('employment-types.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add New Employment Type
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success mb-2">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            @if(\App\Models\User::hasAccess('employment-type-edit/{encryptedId}', true) || \App\Models\User::hasAccess('employment-type-delete/{encryptedId}', true))
                                            <th>Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employmentTypes as $employmentType)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $employmentType->name }}</td>
                                                <td>{{ $employmentType->description ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge {{ $employmentType->is_active ? 'badge-success' : 'badge-danger' }}">
                                                        {{ $employmentType->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                @if(\App\Models\User::hasAccess('employment-type-edit/{encryptedId}', true) || \App\Models\User::hasAccess('employment-type-delete/{encryptedId}', true))
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        @if(\App\Models\User::hasAccess('employment-type-edit/{encryptedId}', true))
                                                        <a href="{{ route('employment-types.edit', ['encryptedId' => Crypt::encrypt($employmentType->id)]) }}"
                                                           class="btn btn-outline-primary"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="Edit Employment Type">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @endif

                                                        @if(\App\Models\User::hasAccess('employment-type-delete/{encryptedId}', true))
                                                        <form action="{{ route('employment-types.destroy', ['encryptedId' => Crypt::encrypt($employmentType->id)]) }}"
                                                              method="POST"
                                                              class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this employment type?')"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Delete Employment Type">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                        @endif
                                                    </div>
                                                </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No employment types found.</td>
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
