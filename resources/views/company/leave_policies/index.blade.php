@extends('layouts.app')

@section('title', 'Leave Policies')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Leave Policies</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Leave Policies</a></div>
            </div>
        </div>
        <section class="card">
            <div class="card-1 card-header">
                <h5 class="mb-0">Company Leave Policies</h5>
                <div class="section-header-button">
                    <a href="{{ route('company.leave-policies.create') }}" class="btn btn-primary">Create New Policy</a>
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

                                @if($policies->isEmpty())
                                <div class="alert alert-info">
                                    <p>No leave policies found. <a href="{{ route('company.leave-policies.create') }}">Create one now</a></p>
                                </div>
                                @else
                                <div class="table-responsive">
                                    <table class="table table-striped" id="leavePoliciesTable">
                                        <thead>
                                            <tr>
                                                <th>Policy Name</th>
                                                <th>Financial Year</th>
                                                <th>Leave Types Count</th>
                                                <th>Status</th>
                                                <th>Created Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($policies as $policy)
                                            <tr>
                                                <td>{{ $policy->name }}</td>
                                                <td>{{ $policy->financialYear->name ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge badge-info">{{ $policy->leaveTypePolicies()->count() }}</span>
                                                </td>
                                                <td>
                                                    @if($policy->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                    @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $policy->created_at->format('d-m-Y') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('company.leave-policies.edit', $policy->id) }}"
                                                        class="btn btn-outline-warning btn-sm action-btn rounded-end-0"
                                                        data-id="{{ $policy->id }}" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Edit Policy"
                                                        aria-label="Edit">
                                                        <span class="btn-content">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                    </a>
                                                    <a href="{{ route('company.leave-policies.manage-leave-types', $policy->id) }}"
                                                        class="btn btn-outline-info btn-sm action-btn rounded-0"
                                                        data-id="{{ $policy->id }}" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Manage Leave Types"
                                                        aria-label="Manage Leave Types">
                                                        <span class="btn-content">
                                                            <i class="fas fa-cog"></i>
                                                        </span>
                                                    </a>
                                                    <form action="{{ route('company.leave-policies.destroy', $policy->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-outline-danger btn-sm action-btn rounded-start-0"
                                                            data-id="{{ $policy->id }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Delete Policy"
                                                            onclick="return confirm('Are you sure?');"
                                                            aria-label="Delete">
                                                            <span class="btn-content">
                                                                <i class="fas fa-trash"></i>
                                                            </span>
                                                        </button>
                                                    </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#leavePoliciesTable').DataTable();
});
</script>
@endpush
