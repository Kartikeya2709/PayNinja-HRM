@extends('layouts.app')

@section('title', 'Designations')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Designations</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ url('/home') }}">Dashboard</a>
                </div>
                <div class="breadcrumb-item">Designations</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Designations</h5>
                        @if(\App\Models\User::hasAccess('designation-create', true))
                        <div class="card-tools">
                            <a href="{{ route('designations.create') }}"
                               class="btn btn-primary"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               title="Add New Designation">
                                <i class="fas fa-plus"></i> Add New Designation
                            </a>
                        </div>
                        @endif
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Level</th>
                                        <th>Department</th>
                                        <th>Description</th>
                                        @if(\App\Models\User::hasAccess('designation-edit/{encryptedId}', true) || \App\Models\User::hasAccess('designation-delete/{encryptedId}', true))
                                        <th>Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($designations as $designation)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $designation->title }}</td>
                                            <td>{{ $designation->level }}</td>
                                            <td>{{ $designation->department_name ?? 'N/A' }}</td>
                                            <td>{{ $designation->description ?? 'N/A' }}</td>
                                            @if(\App\Models\User::hasAccess('designation-edit/{encryptedId}', true) || \App\Models\User::hasAccess('designation-delete/{encryptedId}', true))
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if(\App\Models\User::hasAccess('designation-edit/{encryptedId}', true))
                                                    <a href="{{ route('designations.edit', ['encryptedId' => Crypt::encrypt($designation->id)]) }}"
                                                       class="btn btn-outline-primary"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="Edit Designation">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @endif

                                                    @if(\App\Models\User::hasAccess('designation-delete/{encryptedId}', true))
                                                    <form action="{{ route('designations.destroy', ['encryptedId' => Crypt::encrypt($designation->id)]) }}"
                                                          method="POST"
                                                          class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-outline-danger"
                                                                onclick="return confirm('Are you sure you want to delete this designation?')"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Delete Designation">
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
                                            <td colspan="6" class="text-center">No designations found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
