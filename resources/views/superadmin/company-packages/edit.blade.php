@extends('layouts.app')

@section('title', 'Edit Company Package Assignment')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .assignment-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .detail-row {
            margin-bottom: 10px;
        }
        .status-toggle {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
        }
    </style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Edit Company Package Assignment</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item"><a href="{{ route('superadmin.company-packages.index') }}">Company Packages</a></div>
                        <div class="breadcrumb-item active">Edit</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Edit Assignment Details</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('superadmin.company-packages.show', $companyPackage) }}" class="btn btn-info">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('superadmin.company-packages.update', $companyPackage) }}" method="POST" id="edit-form">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="assignment-info">
                                        <h5>Current Assignment</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Company:</strong> {{ $companyPackage->company->name }}<br>
                                                <strong>Email:</strong> {{ $companyPackage->company->email }}<br>
                                                <strong>Package:</strong> {{ $companyPackage->package->name }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Current Status:</strong> 
                                                <span class="badge badge-{{ $companyPackage->is_active ? 'success' : 'danger' }}">
                                                    {{ $companyPackage->is_active ? 'Active' : 'Inactive' }}
                                                </span><br>
                                                <strong>Assigned Date:</strong> {{ $companyPackage->assigned_at->format('M d, Y H:i') }}<br>
                                                @if($companyPackage->expires_at)
                                                    <strong>Expires:</strong> {{ $companyPackage->expires_at->format('M d, Y') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="assigned_at">Assigned Date *</label>
                                                <input type="datetime-local" 
                                                       name="assigned_at" 
                                                       id="assigned_at" 
                                                       class="form-control" 
                                                       value="{{ $companyPackage->assigned_at->format('Y-m-d\TH:i') }}"
                                                       required>
                                                @error('assigned_at')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="expires_at">Expiry Date</label>
                                                <input type="date" 
                                                       name="expires_at" 
                                                       id="expires_at" 
                                                       class="form-control"
                                                       value="{{ $companyPackage->expires_at ? $companyPackage->expires_at->format('Y-m-d') : '' }}">
                                                @error('expires_at')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="notes">Notes</label>
                                        <textarea name="notes" 
                                                  id="notes" 
                                                  class="form-control" 
                                                  rows="4" 
                                                  placeholder="Add any notes about this assignment...">{{ old('notes', $companyPackage->notes ?? '') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="status-toggle">
                                        <h6>Status Management</h6>
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="is_active" 
                                                   name="is_active"
                                                   {{ $companyPackage->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active Assignment
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Uncheck to deactivate this assignment. This will prevent the company from accessing the package.
                                        </small>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-primary" id="update-btn">
                                            <i class="fas fa-save"></i> Update Assignment
                                        </button>
                                        <a href="{{ route('superadmin.company-packages.index') }}" class="btn btn-secondary">
                                            Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Assignment Details</h4>
                            </div>
                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="detail-label">Company:</span><br>
                                    {{ $companyPackage->company->name }}<br>
                                    <small class="text-muted">{{ $companyPackage->company->email }}</small>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Package:</span><br>
                                    {{ $companyPackage->package->name }}<br>
                                    <small class="text-muted">
                                        {{ ucfirst($companyPackage->package->pricing_type) }} - 
                                        {{ $companyPackage->package->currency }} {{ number_format($companyPackage->package->base_price, 2) }}
                                    </small>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Assigned By:</span><br>
                                    {{ $companyPackage->assignedBy->name ?? 'System' }}
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Created:</span><br>
                                    {{ $companyPackage->created_at->format('M d, Y H:i') }}
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Last Updated:</span><br>
                                    {{ $companyPackage->updated_at->format('M d, Y H:i') }}
                                </div>
                                @if($companyPackage->activated_at)
                                    <div class="detail-row">
                                        <span class="detail-label">Activated:</span><br>
                                        {{ $companyPackage->activated_at->format('M d, Y H:i') }}
                                    </div>
                                @endif
                                @if($companyPackage->deactivated_at)
                                    <div class="detail-row">
                                        <span class="detail-label">Deactivated:</span><br>
                                        {{ $companyPackage->deactivated_at->format('M d, Y H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('superadmin.company-packages.show', $companyPackage) }}" class="btn btn-info btn-block mb-2">
                                    <i class="fas fa-eye"></i> View Full Details
                                </a>
                                <button type="button" 
                                        class="btn btn-warning btn-block mb-2 toggle-active-quick"
                                        data-id="{{ $companyPackage->id }}" 
                                        data-toggle-url="{{ route('superadmin.company-packages.toggle-active', $companyPackage->id) }}"
                                        data-company-name="{{ $companyPackage->company->name }}"
                                        data-current-status="{{ $companyPackage->is_active ? 'active' : 'inactive' }}">
                                    <i class="fas {{ $companyPackage->is_active ? 'fa-pause-circle' : 'fa-play-circle' }}"></i>
                                    {{ $companyPackage->is_active ? 'Deactivate' : 'Activate' }} 
                                </button>
                                <form action="{{ route('superadmin.company-packages.unassign', $companyPackage) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block" 
                                            onclick="return confirm('Are you sure you want to unassign this package? This action cannot be undone.')">
                                        <i class="fas fa-unlink"></i> Unassign Package
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
                            
                            // Use SweetAlert for success message
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message || `Assignment ${action}d successfully`,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                showToast(data.message || `Assignment ${action}d successfully`, 'success');
                            }
                            
                            // Reload page after successful toggle
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
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

            // Quick toggle button
            $('.toggle-active-quick').on('click', function(e) {
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

            // Form submission with AJAX
            $('#edit-form').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $button = $('#update-btn');
                
                // Store original button state
                const originalHtml = $button.html();
                const originalDisabled = $button.prop('disabled');
                
                $.ajax({
                    url: $form.attr('action'),
                    method: 'PUT',
                    data: new FormData($form[0]),
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $button.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
                    },
                    success: function(data) {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message || 'Assignment updated successfully',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                showToast(data.message || 'Assignment updated successfully', 'success');
                            }
                            
                            // Redirect to company packages index after successful update
                            setTimeout(function() {
                                window.location.href = "{{ route('superadmin.company-packages.index') }}";
                            }, 2000);
                        } else {
                            const errorMsg = data.message || 'Failed to update assignment';
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
                    error: function(xhr, status, error) {
                        console.error('Update error:', error, xhr);
                        
                        // Restore button state on error
                        $button.html(originalHtml).prop('disabled', originalDisabled);
                        
                        let errorMessage = 'Error updating assignment';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 419) {
                            errorMessage = 'CSRF token mismatch. Please refresh the page.';
                        } else if (xhr.status === 403) {
                            errorMessage = 'You do not have permission to perform this action.';
                        } else if (xhr.status === 422) {
                            errorMessage = 'Validation error. Please check your input.';
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