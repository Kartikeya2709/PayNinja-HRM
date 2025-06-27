@extends('layouts.app')

@section('title', 'View Beneficiary Badge: ' . $beneficiaryBadge->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1">Beneficiary Badge: {{ $beneficiaryBadge->name }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.beneficiary-badges.index') }}">Beneficiary Badges</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $beneficiaryBadge->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.payroll.beneficiary-badges.edit', $beneficiaryBadge->id) }}" class="btn btn-primary me-2">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                    </a>
                    <a href="{{ route('admin.payroll.beneficiary-badges.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Badge Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">Name</th>
                                    <td>{{ $beneficiaryBadge->name }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>
                                        <span class="badge bg-{{ $beneficiaryBadge->type == 'allowance' ? 'success' : 'danger' }}">
                                            {{ ucfirst($beneficiaryBadge->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Calculation Type</th>
                                    <td>{{ ucfirst($beneficiaryBadge->calculation_type) }}</td>
                                </tr>
                                <tr>
                                    <th>Value</th>
                                    <td>
                                        @if($beneficiaryBadge->calculation_type == 'flat')
                                            {{ number_format($beneficiaryBadge->value, 2) }}
                                        @else
                                            {{ $beneficiaryBadge->value }}%
                                        @endif
                                    </td>
                                </tr>
                                @if($beneficiaryBadge->calculation_type == 'percentage' && $beneficiaryBadge->based_on)
                                <tr>
                                    <th>Based On</th>
                                    <td>{{ $beneficiaryBadge->based_on }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($beneficiaryBadge->is_active)
                                            <span class="badge bg-primary">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Company-wide</th>
                                    <td>
                                        @if($beneficiaryBadge->is_company_wide)
                                            <span class="badge bg-info">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($beneficiaryBadge->description)
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $beneficiaryBadge->description }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $beneficiaryBadge->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $beneficiaryBadge->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- @if($beneficiaryBadge->is_company_wide && $beneficiaryBadge->is_active)
                            <button type="button" class="btn btn-info mb-2" id="applyToAllBtn"> -->
                                <!-- <i class="fa-solid fa-users me-2"></i> Apply to All Employees
                            </button>
                            <div class="alert alert-info"> -->
                                <!-- <i class="fa-solid fa-info-circle me-2"></i>
                                This is a company-wide badge and will be automatically applied to all employees.
                            </div> -->
                        @elseif($beneficiaryBadge->is_active)
                            <button type="button" class="btn btn-outline-info mb-2" id="applyToAllBtn">
                                <i class="fa-solid fa-users me-2"></i> Apply to All Employees
                            </button>
                            <div class="alert alert-warning">
                                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                This badge is not marked as company-wide. Click the button to apply it to all employees.
                            </div>
                        @else
                            <button type="button" class="btn btn-outline-secondary mb-2" disabled>
                                <i class="fa-solid fa-ban me-2"></i> Apply to All Employees
                            </button>
                            <div class="alert alert-secondary">
                                <i class="fa-solid fa-info-circle me-2"></i>
                                This badge is inactive and cannot be applied to employees.
                            </div>
                        @endif

                        <a href="{{ route('admin.payroll.beneficiary-badges.edit', $beneficiaryBadge->id) }}" class="btn btn-primary mb-2">
                            <i class="fa-solid fa-pen-to-square me-2"></i> Edit Badge
                        </a>

                        <form action="{{ route('admin.payroll.beneficiary-badges.destroy', $beneficiaryBadge->id) }}" method="POST" class="d-grid">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete this badge? This action cannot be undone.')">
                                <i class="fa-solid fa-trash me-2"></i> Delete Badge
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const applyToAllBtn = document.getElementById('applyToAllBtn');
        
        if (applyToAllBtn) {
            applyToAllBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to apply this badge to all employees? This action cannot be undone.')) {
                    const btn = this;
                    const originalText = btn.innerHTML;
                    
                    // Disable button and show loading state
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Applying...';
                    
                    // Make AJAX request
                    fetch(`{{ route('admin.payroll.beneficiary-badges.api.apply-to-all', $beneficiaryBadge->id) }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-success alert-dismissible fade show mt-3';
                            alert.role = 'alert';
                            alert.innerHTML = `
                                ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            document.querySelector('.container-fluid').prepend(alert);
                            
                            // Scroll to top to show the message
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            
                            // Update button state
                            btn.innerHTML = '<i class="fa-solid fa-check me-2"></i> Applied to All';
                            btn.classList.remove('btn-outline-info');
                            btn.classList.add('btn-info');
                        } else {
                            throw new Error(data.message || 'Failed to apply badge to all employees');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to apply badge to all employees: ' + error.message);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
                }
            });
        }
    });
</script>
@endpush
@endsection
