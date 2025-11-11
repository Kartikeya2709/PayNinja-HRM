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
                                                    <td>₹ {{ number_format($package->base_price, 2) }}</td>
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
        $(document).ready(function() {
            const $searchInput = $('#search');
            const $pricingTypeSelect = $('#pricing_type');
            const $isActiveSelect = $('#is_active');
            const $perPageSelect = $('#per_page');
            const $packagesTable = $('#packages-tbody');

            let searchTimeout;

            // Function to fetch and update packages
            function fetchPackages() {
                const search = $searchInput.val().trim();
                const pricingType = $pricingTypeSelect.val();
                const isActive = $isActiveSelect.val();
                const perPage = $perPageSelect.val();

                // Build query string
                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (pricingType) params.append('pricing_type', pricingType);
                if (isActive !== '') params.append('is_active', isActive);
                if (perPage) params.append('per_page', perPage);

                // Show loading state
                $packagesTable.html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

                $.ajax({
                    url: '{{ route('superadmin.packages.index') }}',
                    method: 'GET',
                    data: params.toString(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(data) {
                        if (data.html) {
                            $packagesTable.html(data.html);
                        } else {
                            $packagesTable.html('<tr><td colspan="6" class="text-center">Error loading packages</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching packages:', error);
                        $packagesTable.html('<tr><td colspan="6" class="text-center">Error loading packages</td></tr>');
                    }
                });
            }

            // Debounced search
            $searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(fetchPackages, 500);
            });

            // Instant filter for selects
            $pricingTypeSelect.on('change', fetchPackages);
            $isActiveSelect.on('change', fetchPackages);
            $perPageSelect.on('change', fetchPackages);

            // Handle toggle active buttons with AJAX
            $(document).on('click', 'button[type="submit"]', function(e) {
                const $form = $(this).closest('form');
                if ($form.attr('action') && $form.attr('action').indexOf('toggle-active') !== -1) {
                    e.preventDefault();

                    const $button = $(this);
                    const $packageRow = $button.closest('tr');
                    const formData = new FormData($form[0]);
                    
                    // Store original button state for restoration on error
                    const originalHtml = $button.html();
                    const originalDisabled = $button.prop('disabled');

                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function(xhr) {
                            // Show loading state
                            $button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(data) {
                            console.log('Toggle success response:', data);
                            
                            // Always show a message
                            const message = data.message || (data.success ? 'Package status updated successfully' : 'Error updating package status');
                            const type = data.success ? 'success' : 'error';
                            showToast(message, type);
                            
                            // Update UI only if successful
                            if (data.success && data.is_active !== undefined) {
                                const isActive = data.is_active;
                                const $statusCell = $packageRow.find('td:nth-child(5)');
                                const $buttonCell = $packageRow.find('td:nth-child(6)');

                                // Update the status badge
                                if (isActive) {
                                    $statusCell.html('<span class="badge badge-success">Active</span>');
                                    $buttonCell.find('button').removeClass('btn-success').addClass('btn-danger');
                                    $buttonCell.find('button').html('<i class="fas fa-times"></i> Deactivate');
                                } else {
                                    $statusCell.html('<span class="badge badge-danger">Inactive</span>');
                                    $buttonCell.find('button').removeClass('btn-danger').addClass('btn-success');
                                    $buttonCell.find('button').html('<i class="fas fa-check"></i> Activate');
                                }
                            }
                        },
                        complete: function() {
                            // Restore button to original state
                            $button.html(originalHtml).prop('disabled', originalDisabled);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error, xhr);
                            
                            let errorMessage = 'Error updating package status';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 419) {
                                errorMessage = 'CSRF token mismatch. Please refresh the page.';
                            } else if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to perform this action.';
                            } else if (xhr.status === 404) {
                                errorMessage = 'Package not found.';
                            }
                            showToast(errorMessage, 'error');
                        }
                    });
                }
            });

            // Toast notification function
            function showToast(message, type = 'info') {
                console.log('showToast called with:', message, type);
                
                // Try Bootstrap toast first
                if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                    try {
                        // Create toast container if it doesn't exist
                        let $toastContainer = $('.toast-container');
                        if ($toastContainer.length === 0) {
                            $toastContainer = $('<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
                            $('body').append($toastContainer);
                        }

                        // Create toast element
                        const $toast = $(`
                            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                                <div class="d-flex">
                                    <div class="toast-body">${message}</div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                                </div>
                            </div>
                        `);

                        $toastContainer.append($toast);

                        // Initialize and show toast
                        const toast = new bootstrap.Toast($toast[0]);
                        toast.show();

                        // Remove toast after it's hidden
                        $toast.on('hidden.bs.toast', function() {
                            $(this).remove();
                        });
                        
                        return; // Success with Bootstrap toast
                    } catch (e) {
                        console.log('Bootstrap toast failed, falling back to alert:', e);
                    }
                }
                
                // Fallback: use simple alert with styling
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const $alert = $(`
                    <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                
                $('body').append($alert);
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    $alert.alert('close');
                }, 5000);
            }
        });
    </script>
@endpush