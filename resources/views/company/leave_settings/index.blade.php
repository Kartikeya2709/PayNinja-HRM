@extends('layouts.app')

@section('title', 'Leave Settings')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Leave Settings</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Leave Settings</a></div>
            </div>
        </div>

        <section class="card">
            <div class="card-1 card-header">
                <h5 class="mb-0">Leave Settings Configuration</h5>
                <div class="section-header-button">
                    <a href="{{ route('company.leave-types.index') }}" class="btn btn-primary">Back to Leave Types</a>
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
                                    <table class="table table-striped" id="leaveSettingsTable">
                                        <thead>
                                            <tr>
                                                <th>Leave Type</th>
                                                <th>Description</th>
                                                <th>Monthly Limit</th>
                                                <th>Yearly Limit</th>
                                                <th>Disbursement Cycle</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leaveTypes as $leaveType)
                                            <tr>
                                                <td>{{ $leaveType->name }}</td>
                                                <td>{{ $leaveType->description ?? '-' }}</td>
                                                <td>{{ $leaveType->monthly_limit ?? 'Unlimited' }}</td>
                                                <td>{{ $leaveType->yearly_limit ?? 'Unlimited' }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $leaveType->disbursement_cycle)) }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('company.company.leave-settings.edit', $leaveType->id) }}"
                                                           class="btn btn-outline-warning btn-sm action-btn rounded-end-0"
                                                           data-id="{{ $leaveType->id }}" data-bs-toggle="tooltip"
                                                           data-bs-placement="top" title="Configure Settings"
                                                           aria-label="Configure">
                                                            <span class="btn-content">
                                                                <i class="fas fa-cog"></i>
                                                            </span>
                                                            <span class="spinner-border spinner-border-sm d-none"
                                                                  role="status" aria-hidden="true"></span>
                                                        </a>
                                                    </div>
                                                </td>
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
        </section>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#leaveSettingsTable').DataTable();
});
</script>
@endpush
