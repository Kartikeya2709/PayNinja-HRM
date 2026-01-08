@extends('layouts.app')

@section('title', 'Manage Departments')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Manage Departments</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Manage Departments</a></div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Departments</h3>
                            <div class="card-tools">
                                @if(\App\Models\User::hasAccess('department-create', true))
                                <a href="{{ route('departments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add New Department
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success mb-2">{{ session('success') }}</div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            @if(\App\Models\User::hasAccess('department-edit/{encryptedId}', true) || \App\Models\User::hasAccess('department-delete/{encryptedId}', true))
                                            <th>Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($departments as $department)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $department->name }}</td>
                                                <td>{{ $department->description ?? 'N/A' }}</td>
                                                @if(\App\Models\User::hasAccess('department-edit/{encryptedId}', true) || \App\Models\User::hasAccess('department-delete/{encryptedId}', true))
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        @if(\App\Models\User::hasAccess('department-edit/{encryptedId}', true))
                                                        <a href="{{ route('departments.edit', ['encryptedId' => Crypt::encrypt($department->id)]) }}"
                                                           class="btn btn-outline-info"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="Edit Department">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @endif

                                                        @if(\App\Models\User::hasAccess('department-delete/{encryptedId}', true))
                                                        <form action="{{ route('departments.destroy', ['encryptedId' => Crypt::encrypt($department->id)]) }}"
                                                              method="POST"
                                                              class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this department?')"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Delete Department">
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
                                                <td colspan="{{ \App\Models\User::hasAccess('department-edit/{encryptedId}', true) || \App\Models\User::hasAccess('department-delete/{encryptedId}', true) ? '4' : '3' }}" class="text-center">No departments found.</td>
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
    </section>
</div>

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@endsection
