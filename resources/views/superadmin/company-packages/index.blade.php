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
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                                <a href="{{ route('superadmin.company-packages.assign.form') }}" class="btn btn-primary">
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
                                                <tr data-id="{{ $assignment->id }}">
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
                                                            <a href="{{ route('superadmin.company-packages.edit', $assignment) }}" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-sm {{ $assignment->is_active ? 'btn-danger' : 'btn-success' }} toggle-active-btn" 
                                                                    data-id="{{ $assignment->id }}" 
                                                                    data-toggle-url="{{ route('superadmin.company-packages.toggle-active', $assignment->id) }}"
                                                                    data-company-name="{{ $assignment->company->name }}"
                                                                    data-current-status="{{ $assignment->is_active ? 'active' : 'inactive' }}">
                                                                <i class="fas {{ $assignment->is_active ? 'fa-pause-circle' : 'fa-play-circle' }}"></i> 
                                                                {{ $assignment->is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
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
        $(document).ready(function() {
            // Function to perform the toggle action
            function performToggleAction($button, action) {
                const toggleUrl = $button.data('toggle-url');
                const originalHtml = $button.html();
                const originalDisabled = $button.prop('disabled');
                
                $.ajax({
                    url: toggleUrl,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                    },
                    success: function(data) {
                        if (data.success) {
                            const newAction = data.is_active ? 'Deactivate' : 'Activate';
                            const newIcon = data.is_active ? 'fa-pause-circle' : 'fa-play-circle';
                            const newClass = data.is_active ? 'btn-danger' : 'btn-success';
                            
                            $button.removeClass('btn-danger btn-success').addClass(newClass);
                            $button.html(`<i class="fas ${newIcon}"></i> ${newAction}`);
                            $button.data('current-status', data.is_active ? 'active' : 'inactive');
                            
                            // Update status badge
                            const $row = $button.closest('tr');
                            const newStatus = data.is_active ? 'Active' : 'Inactive';
                            const statusClass = data.is_active ? 'badge-success' : 'badge-warning';
                            $row.find('td:nth-child(5)').html(`<span class="badge ${statusClass}">${newStatus}</span>`);
                            
                            // Use SweetAlert for success message
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message || `Package assignment ${action}d successfully`,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                showToast(data.message || `Package assignment ${action}d successfully`, 'success');
                            }
                        } else {
                            const errorMsg = data.message || 'Failed to update assignment status';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Error!',
                                    text: errorMsg,
                                    icon: 'error',
                                    timer: 3000,
                                    showConfirmButton: true
                                });
                            } else {
                                showToast(errorMsg, 'error');
                            }
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', originalDisabled);
                    },
                    error: function(xhr, status, error) {
                        console.error('Toggle error:', error, xhr);
                        
                        // Restore button state on error
                        $button.html(originalHtml).prop('disabled', originalDisabled);
                        
                        let errorMessage = 'Error updating assignment status';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 419) {
                            errorMessage = 'CSRF token mismatch. Please refresh the page.';
                        } else if (xhr.status === 403) {
                            errorMessage = 'You do not have permission to perform this action.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'Assignment not found.';
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                timer: 3000,
                                showConfirmButton: true
                            });
                        } else {
                            showToast(errorMessage, 'error');
                        }
                    }
                });
            }

            // Toggle active/inactive assignment
            $(document).on('click', '.toggle-active-btn', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const companyName = $button.data('company-name');
                const currentStatus = $button.data('current-status');
                const isCurrentlyActive = currentStatus === 'active';
                
                const action = isCurrentlyActive ? 'deactivate' : 'activate';
                const confirmMessage = isCurrentlyActive ? 
                    `Are you sure you want to deactivate this package assignment for ${companyName}?` : 
                    `Are you sure you want to activate this package assignment for ${companyName}?`;
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Confirm Action',
                        text: confirmMessage,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, ' + action + ' it!',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performToggleAction($button, action);
                        }
                    });
                } else {
                    // Fallback to regular confirm if SweetAlert is not available
                    if (confirm(confirmMessage)) {
                        performToggleAction($button, action);
                    }
                }
            });

            const $bulkActionSelect = $('#bulk_action');
            const $applyBulkBtn = $('#apply-bulk');

            $bulkActionSelect.on('change', function() {
                $applyBulkBtn.prop('disabled', !$(this).val());
            });

            // Toast notification function (same as package pages)
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