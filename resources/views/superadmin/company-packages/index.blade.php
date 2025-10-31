@extends('layouts.app')

@section('title', 'Company Package Assignments')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .table-responsive { overflow-x: auto; }
        .status-active { color: green; }
        .status-inactive { color: red; }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Company Package Assignments</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item active">Company Packages</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>All Company Package Assignments</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('superadmin.company-packages.assign') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Assign Package
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <form method="GET" action="{{ route('superadmin.company-packages.index') }}">
                                            <div class="input-group">
                                                <input type="text" name="company_search" class="form-control" placeholder="Search companies..." value="{{ request('company_search') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-4">
                                        <form method="GET" action="{{ route('superadmin.company-packages.index') }}">
                                            <div class="input-group">
                                                <input type="text" name="package_search" class="form-control" placeholder="Search packages..." value="{{ request('package_search') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-4">
                                        <form method="GET" action="{{ route('superadmin.company-packages.index') }}">
                                            <div class="input-group">
                                                <select name="status" class="form-control">
                                                    <option value="">All Status</option>
                                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Company</th>
                                                <th>Package</th>
                                                <th>Assigned Date</th>
                                                <th>Expiry Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($companyPackages ?? [] as $assignment)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $assignment->company->name }}</strong><br>
                                                            <small class="text-muted">{{ $assignment->company->email }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $assignment->package->name }}</strong><br>
                                                            <small class="text-muted">{{ ucfirst($assignment->package->pricing_type) }} - {{ $assignment->package->currency }} {{ number_format($assignment->package->base_price, 2) }}</small>
                                                        </div>
                                                    </td>
                                                    <td>{{ $assignment->assigned_at->format('M d, Y') }}</td>
                                                    <td>{{ $assignment->expires_at ? $assignment->expires_at->format('M d, Y') : 'N/A' }}</td>
                                                    <td>
                                                        @if($assignment->is_active)
                                                            <span class="badge badge-success">Active</span>
                                                        @elseif($assignment->expires_at && $assignment->expires_at->isPast())
                                                            <span class="badge badge-danger">Expired</span>
                                                        @else
                                                            <span class="badge badge-warning">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('superadmin.company-packages.show', $assignment) }}" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-warning" onclick="editAssignment({{ $assignment->id }})">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <form action="{{ route('superadmin.company-packages.destroy', $assignment) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this package assignment?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i> Remove
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No company package assignments found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if(isset($companyPackages) && $companyPackages->hasPages())
                                    <div class="d-flex justify-content-center">
                                        {{ $companyPackages->appends(request()->query())->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Bulk Actions</h4>
                            </div>
                            <div class="card-body">
                                <form id="bulk-action-form" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <select name="bulk_action" id="bulk_action" class="form-control">
                                                <option value="">Select Action</option>
                                                <option value="activate">Activate Selected</option>
                                                <option value="deactivate">Deactivate Selected</option>
                                                <option value="delete">Delete Selected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" id="apply-bulk" class="btn btn-primary" disabled>Apply to Selected</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function editAssignment(id) {
            // Implement edit functionality - could open a modal or redirect
            window.location.href = '{{ route("superadmin.company-packages.edit", ":id") }}'.replace(':id', id);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const bulkActionSelect = document.getElementById('bulk_action');
            const applyBulkBtn = document.getElementById('apply-bulk');

            bulkActionSelect.addEventListener('change', function() {
                applyBulkBtn.disabled = !this.value;
            });

            // Add checkboxes for bulk selection if needed
            // This would require additional implementation for selecting rows
        });
    </script>
@endpush