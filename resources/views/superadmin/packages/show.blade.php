@extends('layouts.app')

@section('title', 'Package Details')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .detail-row {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .status-active { color: green; }
        .status-inactive { color: red; }

        .modules-container {
            max-height: 650px;
            overflow-y: auto;
            padding-right: 5px;
        }

        /* Card style */
        .module-card {
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .module-card:hover {
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        /* Header */
        .module-header {
            background: #f8f9fa;
            padding: 14px 20px;
            border-radius: 10px 10px 0 0;
        }

        /* Checkboxes */
        .form-check-input {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            cursor: pointer;
            border: 1.5px solid #ccc;
            transition: all 0.2s ease-in-out;
        }

        .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .form-check-input:hover {
            transform: scale(1.1);
        }

        /* Labels */
        .module-label {
            font-size: 16px;
            color: #111827;
        }

        .form-check-label {
            font-size: 14px;
            color: #374151;
            cursor: pointer;
        }

        /* Count badge */
        .text-muted.small {
            background: #f1f3f5;
            padding: 4px 10px;
            border-radius: 20px;
        }

        /* Body */
        .card-body {
            background: #fff;
            padding: 18px 22px;
            border-radius: 0 0 10px 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .col-md-4 {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Package Details</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item"><a href="{{ route('superadmin.packages.index') }}">Packages</a></div>
                        <div class="breadcrumb-item active">{{ $package->name }}</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Package Information</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('superadmin.packages.edit', $package) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('superadmin.packages.destroy', $package) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this package?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-row">
                                            <span class="detail-label">Name:</span> {{ $package->name }}
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Pricing Type:</span> {{ ucfirst($package->pricing_type) }}
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Base Price:</span> ₹ {{ number_format($package->base_price, 2) }}
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Status:</span>
                                            <span class="badge badge-{{ $package->is_active ? 'success' : 'danger' }}">
                                                {{ $package->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @if($package->pricing_type == 'recurring')
                                            <div class="detail-row">
                                                <span class="detail-label">Billing Cycle:</span> {{ ucfirst($package->billing_cycle) }}
                                            </div>
                                        @endif
                                        <div class="detail-row">
                                            <span class="detail-label">Created:</span> {{ $package->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Last Updated:</span> {{ $package->updated_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Description:</span>
                                    <p>{{ $package->description ?: 'No description provided.' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Included Modules</h4>
                            </div>
                            <div class="card-body">
                                @if($package->modules && is_array($package->modules))
                                    @php
                                        $enabledModules = array_filter($package->modules, function($enabled) {
                                            return $enabled === true;
                                        });
                                    @endphp
                                    @if(count($enabledModules) > 0)
                                        <div class="modules-container">
                                            {{-- 🔘 Module List --}}
                                            @php
                                                $slugs = \App\Models\Slug::with('children')->root()->orderBy('sort_order')->get();
                                            @endphp
                                            @foreach($slugs as $slug)
                                                @if(!$slug->parent && isset($enabledModules[$slug->slug]) && $enabledModules[$slug->slug] === true)
                                                <div class="module-card card mb-3 shadow-sm border-0">
                                                    <div class="card-header bg-white border-bottom module-header d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <input type="checkbox"
                                                                   checked
                                                                   disabled
                                                                   class="form-check-input module-main-checkbox me-2">
                                                            <label class="fw-bold mb-0 module-label">
                                                                @if($slug->icon)
                                                                    <i class="{{ $slug->icon }} me-2 text-success"></i>
                                                                @endif
                                                                {{ $slug->name }}
                                                            </label>
                                                        </div>

                                                        <span class="text-muted small">
                                                            @php
                                                                $childCount = $slug->children ? $slug->children->count() : 0;
                                                                $selectedCount = 0;
                                                                if ($slug->children) {
                                                                    foreach ($slug->children as $child) {
                                                                        if (isset($enabledModules[$child->slug]) && $enabledModules[$child->slug] === true) {
                                                                            $selectedCount++;
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                            {{ $selectedCount }} of {{ $childCount }} enabled
                                                        </span>
                                                    </div>

                                                    {{-- Always visible children --}}
                                                    @if($slug->children && $slug->children->count() > 0)
                                                    <div class="card-body">
                                                        <div class="row">
                                                            @foreach($slug->children->sortBy('sort_order') as $child)
                                                            <div class="col-md-4 col-sm-6 mb-2">
                                                                <div class="form-check">
                                                                    <input type="checkbox"
                                                                           {{ (isset($enabledModules[$child->slug]) && $enabledModules[$child->slug] === true) ? 'checked' : '' }}
                                                                           disabled
                                                                           class="form-check-input module-sub-checkbox">
                                                                    <label class="form-check-label">
                                                                        {{ $child->name }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <p>No modules enabled for this package.</p>
                                    @endif
                                @else
                                    <p>No modules assigned to this package.</p>
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Pricing Tiers</h4>
                            </div>
                            <div class="card-body">
                                @if($package->pricingTiers && $package->pricingTiers->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Tier Name</th>
                                                    <th>Min Users</th>
                                                    <th>Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($package->pricingTiers as $tier)
                                                    <tr>
                                                        <td>{{ $tier->name }}</td>
                                                        <td>{{ $tier->min_users }}</td>
                                                        <td>₹ {{ number_format($tier->price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p>No pricing tiers defined.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Assigned Companies</h4>
                            </div>
                            <div class="card-body">
                                @if($package->companies && $package->companies->count() > 0)
                                    <div class="list-group">
                                        @foreach($package->companies as $company)
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">{{ $company->name }}</h6>
                                                    <small>{{ $company->pivot->assigned_at->format('M d, Y') }}</small>
                                                </div>
                                                <p class="mb-1">{{ $company->email }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p>No companies assigned to this package.</p>
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('superadmin.packages.edit', $package) }}" class="btn btn-warning btn-block mb-2">
                                    <i class="fas fa-edit"></i> Edit Package
                                </a>
                                <a href="{{ route('superadmin.company-packages.assign', ['package' => $package->id]) }}" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-plus"></i> Assign to Company
                                </a>
                                <form action="{{ route('superadmin.packages.toggle-active', $package) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $package->is_active ? 'danger' : 'success' }} btn-block">
                                        <i class="fas fa-{{ $package->is_active ? 'times' : 'check' }}"></i>
                                        {{ $package->is_active ? 'Deactivate' : 'Activate' }} Package
                                    </button>
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
        $(document).ready(function() {
            // Handle toggle active buttons with AJAX (same functionality as index.blade.php)
            $(document).on('click', 'button[type="submit"]', function(e) {
                const $form = $(this).closest('form');
                if ($form.attr('action') && $form.attr('action').indexOf('toggle-active') !== -1) {
                    e.preventDefault();

                    const $button = $(this);
                    
                    // Store original button state for restoration on error
                    const originalHtml = $button.html();
                    const originalDisabled = $button.prop('disabled');

                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: new FormData($form[0]),
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
                                
                                // Update status badge in the details section
                                const $statusElement = $('.card-body .badge');
                                if (isActive) {
                                    $statusElement.removeClass('badge-danger').addClass('badge-success').text('Active');
                                } else {
                                    $statusElement.removeClass('badge-success').addClass('badge-danger').text('Inactive');
                                }
                                
                                // Update button state
                                $button.removeClass('btn-danger btn-success')
                                       .addClass(isActive ? 'btn-danger' : 'btn-success')
                                       .html(`<i class="fas fa-${isActive ? 'times' : 'check'}"></i> ${isActive ? 'Deactivate' : 'Activate'} Package`);
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

            // Toast notification function (same as index.blade.php)
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