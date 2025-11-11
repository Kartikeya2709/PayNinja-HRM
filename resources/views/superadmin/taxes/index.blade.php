@extends('layouts.app')

@section('title', 'Tax Management')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .table-responsive { overflow-x: auto; }
        .tax-rate {
            font-weight: bold;
            color: #28a745;
        }
        .status-active { color: green; }
        .status-inactive { color: red; }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Tax Management</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item active">Taxes</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>All Tax Rates</h4>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createTaxModal">
                                        <i class="fas fa-plus"></i> Create Tax Rate
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <form method="GET" action="{{ route('superadmin.taxes.index') }}">
                                            <div class="input-group">
                                                <input type="text" name="search" class="form-control" placeholder="Search tax names..." value="{{ request('search') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <form method="GET" action="{{ route('superadmin.taxes.index') }}">
                                            <div class="input-group">
                                                <select name="status" class="form-control">
                                                    <option value="">All Status</option>
                                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                                                <th>Name</th>
                                                <th>Rate</th>
                                                <th>Type</th>
                                                <th>Country</th>
                                                <th>State/Region</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($taxes ?? [] as $tax)
                                                <tr>
                                                    <td>{{ $tax->name }}</td>
                                                    <td><span class="tax-rate">{{ $tax->rate }}%</span></td>
                                                    <td>{{ ucfirst($tax->type) }}</td>
                                                    <td>{{ $tax->country ?: 'All Countries' }}</td>
                                                    <td>{{ $tax->state ?: 'All States' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $tax->is_active ? 'success' : 'danger' }}">
                                                            {{ $tax->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-info" onclick="viewTax({{ $tax->id }})">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-warning" onclick="editTax({{ $tax->id }})">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <form action="{{ route('superadmin.taxes.toggle', $tax) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-{{ $tax->is_active ? 'danger' : 'success' }}">
                                                                <i class="fas fa-{{ $tax->is_active ? 'times' : 'check' }}"></i>
                                                                {{ $tax->is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('superadmin.taxes.destroy', $tax) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this tax rate?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">No tax rates found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if(isset($taxes) && $taxes->hasPages())
                                    <div class="d-flex justify-content-center">
                                        {{ $taxes->appends(request()->query())->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Create Tax Modal -->
    <div class="modal fade" id="createTaxModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Tax Rate</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('superadmin.taxes.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Tax Name</label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rate">Tax Rate (%)</label>
                                    <input type="number" step="0.01" name="rate" id="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ old('rate') }}" required>
                                    @error('rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Tax Type</label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="inclusive" {{ old('type') == 'inclusive' ? 'selected' : '' }}>Inclusive</option>
                                        <option value="exclusive" {{ old('type') == 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Country (Optional)</label>
                                    <input type="text" name="country" id="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country') }}" placeholder="Leave empty for all countries">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">State/Region (Optional)</label>
                                    <input type="text" name="state" id="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state') }}" placeholder="Leave empty for all states">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label for="is_active" class="form-check-label">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Tax Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // View tax function
            window.viewTax = function(id) {
                // Implement view functionality
                window.location.href = '{{ route("superadmin.taxes.show", ":id") }}'.replace(':id', id);
            };

            // Edit tax function
            window.editTax = function(id) {
                // Implement edit functionality
                window.location.href = '{{ route("superadmin.taxes.edit", ":id") }}'.replace(':id', id);
            };
        });
    </script>
@endpush