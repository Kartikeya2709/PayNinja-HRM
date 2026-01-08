@extends('layouts.app')

@section('title', 'Leave Types')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Leave Types</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Leave Types</a></div>
            </div>
        </div>
        <section class="card">
            <div class="card-1 card-header">
                <h5 class="mb-0">Leave Types</h5>
                <div class="section-header-button">
                @if(\App\Models\User::hasAccess('leaves/leave-type-create', true))
                <a href="{{ route('leaves.leave-types.create') }}" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Add New Leave Type">
                    <i class="fas fa-plus"></i> Add New Leave Type
                </a>
                @endif
            </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12 px-0">
                        <div class="card">
                            <div class="card-body">
                                @if(session('success'))
                                <div class="alert alert-success alert-dismissible show fade">
                                    <div class="alert-body">
                                        <button class="close" data-dismiss="alert">
                                            <span>&times;</span>
                                        </button>
                                        {{ session('success') }}
                                    </div>
                                </div>
                                @endif

                                @if(session('error'))
                                <div class="alert alert-danger alert-dismissible show fade">
                                    <div class="alert-body">
                                        <button class="close" data-dismiss="alert">
                                            <span>&times;</span>
                                        </button>
                                        {{ session('error') }}
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-striped" id="leaveTypesTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Default Days</th>
                                                <th>Requires Attachment</th>
                                                <th>Status</th>
                                                @if(\App\Models\User::hasAccess('leaves/leave-type-edit/{encryptedId}', true) || \App\Models\User::hasAccess('leaves/leave-type-delete/{encryptedId}', true))
                                                <th>Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leaveTypes as $leaveType)
                                            <tr>
                                                <td>{{ $leaveType->name }}</td>
                                                <td>{{ $leaveType->description ?? '-' }}</td>
                                                <td>{{ $leaveType->default_days }}</td>
                                                <td>
                                                    @if($leaveType->requires_attachment)
                                                    <span class="badge badge-info">Yes</span>
                                                    @else
                                                    <span class="badge badge-secondary">No</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($leaveType->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                    @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                @if(\App\Models\User::hasAccess('leaves/leave-type-edit/{encryptedId}', true) || \App\Models\User::hasAccess('leaves/leave-type-delete/{encryptedId}', true))
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                    @if(\App\Models\User::hasAccess('leaves/leave-type-edit/{encryptedId}', true))
                                                    <a href="{{ route('leaves.leave-types.edit', \Illuminate\Support\Facades\Crypt::encrypt($leaveType->id)) }}"
                                                        class="btn btn-outline-warning btn-sm action-btn {{ \App\Models\User::hasAccess('leaves/leave-type-delete/{encryptedId}', true) ? 'rounded-end-0' : 'rounded' }}"
                                                        data-id="{{ $leaveType->id }}" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Edit Leave Type"
                                                        aria-label="Edit">
                                                        <span class="btn-content">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        <span class="spinner-border spinner-border-sm d-none"
                                                            role="status" aria-hidden="true"></span>
                                                    </a>
                                                    @endif

                                                    @if(\App\Models\User::hasAccess('leaves/leave-type-delete/{encryptedId}', true))
                                                    <form
                                                        action="{{ route('leaves.leave-types.destroy', \Illuminate\Support\Facades\Crypt::encrypt($leaveType->id)) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Are you sure you want to delete this leave type?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-outline-danger btn-sm action-btn {{ \App\Models\User::hasAccess('leaves/leave-type-edit/{encryptedId}', true) ? 'rounded-start-0' : 'rounded' }}"
                                                            data-id="{{ $leaveType->id ?? '' }}"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Delete Leave Type" aria-label="Delete">
                                                            <span class="btn-content">
                                                                <i class="fas fa-trash"></i>
                                                            </span>
                                                            <span class="spinner-border spinner-border-sm d-none"
                                                                role="status" aria-hidden="true"></span>
                                                        </button>
                                                    </form>
                                                    @endif
                                                    </div>
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</div>
</section>
@endsection

@push('scripts')
<script>
    // Initialize DataTable
    $('#leaveTypesTable').DataTable();

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush
