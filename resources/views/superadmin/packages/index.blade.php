@extends('layouts.app')

@section('title', 'Packages')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .status-active { color: green; }
        .status-inactive { color: red; }
        .table-responsive { overflow-x: auto; }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Packages</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item active">Packages</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>All Packages</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('superadmin.packages.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create New Package
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input type="text" id="search" class="form-control" placeholder="Search packages..." value="{{ request('search') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <select id="pricing_type" class="form-control">
                                                <option value="">All Pricing Types</option>
                                                <option value="one_time" {{ request('pricing_type') == 'one_time' ? 'selected' : '' }}>One-time</option>
                                                <option value="subscription" {{ request('pricing_type') == 'subscription' ? 'selected' : '' }}>Subscription</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <select id="is_active" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <select id="per_page" class="form-control">
                                                <option value="10" {{ request('per_page', 15) == '10' ? 'selected' : '' }}>10 per page</option>
                                                <option value="15" {{ request('per_page', 15) == '15' ? 'selected' : '' }}>15 per page</option>
                                                <option value="25" {{ request('per_page', 15) == '25' ? 'selected' : '' }}>25 per page</option>
                                                <option value="50" {{ request('per_page', 15) == '50' ? 'selected' : '' }}>50 per page</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="packages-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Pricing Type</th>
                                                <th>Base Price</th>
                                                <th>Billing Cycle</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="packages-tbody">
                                            @forelse($packages ?? [] as $package)
                                                <tr data-package-id="{{ $package->id }}">
                                                    <td>{{ $package->name }}</td>
                                                    <td>{{ ucfirst(str_replace('_', '-', $package->pricing_type)) }}</td>
                                                    <td>{{ $package->currency }} {{ number_format($package->base_price, 2) }}</td>
                                                    <td>{{ $package->billing_cycle ? ucfirst($package->billing_cycle) : '-' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $package->is_active ? 'success' : 'danger' }}">
                                                            {{ $package->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('superadmin.packages.show', $package) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        <a href="{{ route('superadmin.packages.edit', $package) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <form action="{{ route('superadmin.packages.toggle-active', $package) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-{{ $package->is_active ? 'danger' : 'success' }}">
                                                                <i class="fas fa-{{ $package->is_active ? 'times' : 'check' }}"></i>
                                                                {{ $package->is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr id="no-packages-row">
                                                    <td colspan="6" class="text-center">No packages found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if(isset($packages) && $packages->hasPages())
                                    <div class="d-flex justify-content-center">
                                        {{ $packages->appends(request()->query())->links() }}
                                    </div>
                                @endif
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
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const pricingTypeSelect = document.getElementById('pricing_type');
            const isActiveSelect = document.getElementById('is_active');
            const perPageSelect = document.getElementById('per_page');
            const packagesTable = document.getElementById('packages-tbody');
            const noPackagesRow = document.getElementById('no-packages-row');

            let searchTimeout;

            // Function to fetch and update packages
            function fetchPackages() {
                const search = searchInput.value.trim();
                const pricingType = pricingTypeSelect.value;
                const isActive = isActiveSelect.value;
                const perPage = perPageSelect.value;

                // Build query string
                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (pricingType) params.append('pricing_type', pricingType);
                if (isActive !== '') params.append('is_active', isActive);
                if (perPage) params.append('per_page', perPage);

                // Show loading state
                packagesTable.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

                fetch(`{{ route('superadmin.packages.index') }}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        packagesTable.innerHTML = data.html;
                    } else {
                        packagesTable.innerHTML = '<tr><td colspan="6" class="text-center">Error loading packages</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching packages:', error);
                    packagesTable.innerHTML = '<tr><td colspan="6" class="text-center">Error loading packages</td></tr>';
                });
            }

            // Debounced search
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(fetchPackages, 500);
            });

            // Instant filter for selects
            pricingTypeSelect.addEventListener('change', fetchPackages);
            isActiveSelect.addEventListener('change', fetchPackages);
            perPageSelect.addEventListener('change', fetchPackages);

            // Handle toggle active buttons with AJAX
            document.addEventListener('click', function(e) {
                if (e.target.matches('button[type="submit"]') &&
                    e.target.closest('form[action*="toggle-active"]')) {
                    e.preventDefault();

                    const form = e.target.closest('form');
                    const formData = new FormData(form);
                    const packageRow = e.target.closest('tr');

                    // Show loading state
                    e.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    e.target.disabled = true;

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the status badge
                            const statusCell = packageRow.querySelector('td:nth-child(5)');
                            const buttonCell = packageRow.querySelector('td:nth-child(6)');

                            if (data.is_active) {
                                statusCell.innerHTML = '<span class="badge badge-success">Active</span>';
                                buttonCell.querySelector('button').className = 'btn btn-sm btn-danger';
                                buttonCell.querySelector('button').innerHTML = '<i class="fas fa-times"></i> Deactivate';
                            } else {
                                statusCell.innerHTML = '<span class="badge badge-danger">Inactive</span>';
                                buttonCell.querySelector('button').className = 'btn btn-sm btn-success';
                                buttonCell.querySelector('button').innerHTML = '<i class="fas fa-check"></i> Activate';
                            }

                            // Show success message
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message || 'Error updating package status', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error updating package status', 'error');
                    })
                    .finally(() => {
                        e.target.disabled = false;
                    });
                }
            });

            // Toast notification function
            function showToast(message, type = 'info') {
                // Create toast container if it doesn't exist
                let toastContainer = document.querySelector('.toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                    toastContainer.style.zIndex = '9999';
                    document.body.appendChild(toastContainer);
                }

                // Create toast element
                const toast = document.createElement('div');
                toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
                toast.setAttribute('role', 'alert');
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;

                toastContainer.appendChild(toast);

                // Initialize and show toast
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();

                // Remove toast after it's hidden
                toast.addEventListener('hidden.bs.toast', () => {
                    toast.remove();
                });
            }
        });
    </script>
@endpush