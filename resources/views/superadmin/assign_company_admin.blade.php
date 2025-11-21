@extends('layouts.app')

@section('title', $pageTitle ?? (isset($admin) ? 'Edit Company Admin' : 'Create Company Admin'))

@section('content')
<div class="main-content-01">
        <div class="section-header">
            <h1>{{ $pageTitle ?? (isset($admin) ? 'Edit Company Admin' : 'Create Company Admin') }}</h1>
        </div>
        
        <div class="section-body">
            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form
                action="{{ $formAction ?? '' }}"
                method="POST"
                id="assignCompanyAdminForm"
            >
                @csrf
                @if(isset($admin))
                    @method('PUT')
                @endif

                <div class="row">
                    {{-- LEFT COLUMN: Company Information --}}
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header justify-content-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-building mr-2"></i>
                                    Company Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="company_id" class="form-label">
                                        Select Company <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        name="company_id"
                                        id="company_id"
                                        class="form-control @error('company_id') is-invalid @enderror"
                                        {{ isset($admin) ? 'readonly disabled' : '' }}
                                    >
                                        <option value="">-- Select Company --</option>
                                        @foreach($companies as $company)
                                            @php
                                                $isCurrentCompany = isset($admin) && old('company_id', $admin->company_id) == $company->id;
                                                $isAssigned = isset($assignedCompanies) && in_array($company->id, $assignedCompanies);
                                                $employeeCount = $company->employees->count();
                                                $adminCount = $company->employees->filter(function($employee) {
                                                    return $employee->user && $employee->user->role === 'company_admin';
                                                })->count();
                                            @endphp
                                            <option
                                                value="{{ $company->id }}"
                                                data-company-data="{{ json_encode([
                                                    'id' => $company->id,
                                                    'name' => $company->name,
                                                    'domain' => $company->domain,
                                                    'email' => $company->email,
                                                    'phone' => $company->phone,
                                                    'address' => $company->address,
                                                    'employeeCount' => $employeeCount,
                                                    'adminCount' => $adminCount,
                                                    'departmentCount' => $company->departments->count(),
                                                    'designationCount' => $company->designations->count(),
                                                    'userCount' => $company->users->count(),
                                                    'documentCount' => $company->documents->count()
                                                ]) }}"
                                                {{ $isCurrentCompany || old('company_id') == $company->id ? 'selected' : '' }}
                                                @if($isAssigned && !$isCurrentCompany)
                                                    disabled
                                                    data-reason="already_assigned"
                                                @endif
                                            >
                                                {{ $company->name }}
                                                @if($isAssigned && !$isCurrentCompany)
                                                    (Assigned)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    @if(isset($admin))
                                        <input type="hidden" name="company_id" value="{{ $admin->company_id }}">
                                    @endif
                                    
                                    <div class="invalid-feedback">
                                        @error('company_id')
                                            {{ $message }}
                                        @else
                                            Please select a company.
                                        @enderror
                                    </div>
                                </div>

                                {{-- Dynamic Company Information Display --}}
                                <div id="companyInfoCard" class="company-info-card mt-3 p-3 bg-light rounded border" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-primary mb-0">
                                            <i class="fas fa-building mr-2"></i>
                                            <span id="companyInfoTitle">Company Information</span>
                                        </h6>
                                        <span id="companyStatusBadge" class="badge badge-secondary">No Company Selected</span>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="company-detail-item">
                                                <small class="text-muted">Company Name</small>
                                                <div class="font-weight-bold" id="companyName">-</div>
                                            </div>
                                            <div class="company-detail-item mt-2">
                                                <small class="text-muted">Domain</small>
                                                <div class="font-weight-bold" id="companyDomain">-</div>
                                            </div>
                                            <div class="company-detail-item mt-2">
                                                <small class="text-muted">Contact Email</small>
                                                <div class="font-weight-bold" id="companyEmail">-</div>
                                            </div>
                                            <div class="company-detail-item mt-2">
                                                <small class="text-muted">Phone</small>
                                                <div class="font-weight-bold" id="companyPhone">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="company-detail-item">
                                                <small class="text-muted">Employees</small>
                                                <div class="font-weight-bold">
                                                    <span id="employeeCount">0</span> Total
                                                    <small class="text-muted">(<span id="adminCount">0</span> Admin{{ ($admin->employee_count ?? 0) !== 1 ? 's' : '' }})</small>
                                                </div>
                                            </div>
                                            <div class="company-detail-item mt-2">
                                                <small class="text-muted">Users</small>
                                                <div class="font-weight-bold" id="userCount">0</div>
                                            </div>
                                            <div class="company-detail-item mt-2">
                                                <small class="text-muted">Departments</small>
                                                <div class="font-weight-bold" id="departmentCount">0</div>
                                            </div>
                                            <div class="company-detail-item mt-2">
                                                <small class="text-muted">Designations</small>
                                                <div class="font-weight-bold" id="designationCount">0</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="company-detail-item">
                                                <small class="text-muted">Address</small>
                                                <div class="font-weight-bold" id="companyAddress">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Edit Mode Current Assignment Card --}}
                                @if(isset($admin) && $admin->company)
                                    <div class="current-assignment-card mt-3 p-3 border rounded" style="border-left: 4px solid #007bff;">
                                        <h6 class="text-success mb-2">
                                            <i class="fas fa-user-shield mr-2"></i>
                                            Current Assignment
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">Assigned Company</small>
                                                <div class="font-weight-bold">{{ $admin->company->name ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Employee Code</small>
                                                <div class="font-weight-bold">{{ $admin->employee_code ?? 'Auto-generated' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: Admin Information --}}
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header justify-content-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-edit mr-2"></i>
                                    Admin Information
                                </h5>
                            </div>
                            <div class="card-body">
                                {{-- Personal Details Section --}}
                                <h6 class="text-muted mb-3">
                                    <i class="fas fa-user mr-2"></i>
                                    Personal Details
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">
                                                Full Name <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                name="name"
                                                id="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name', isset($admin) ? $admin->user->name : '') }}"
                                            >
                                            @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @else
                                                <div class="invalid-feedback">Please provide a valid name.</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">
                                                Email Address <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="email"
                                                name="email"
                                                id="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', isset($admin) ? $admin->user->email : '') }}"
                                            >
                                            @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @else
                                                <div class="invalid-feedback">Please provide a valid email address.</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Contact Details Section --}}
                                <h6 class="text-muted mb-3 mt-4">
                                    <i class="fas fa-phone mr-2"></i>
                                    Contact Details
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">
                                                Phone Number <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">+91</span>
                                                </div>
                                                <input
                                                    type="tel"
                                                    name="phone"
                                                    maxlength="10"
                                                    id="phone"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    value="{{ old('phone', $admin->phone ?? '') }}"
                                                >
                                            </div>
                                        </div>
                                        @error('phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                            <select 
                                                name="gender" 
                                                id="gender" 
                                                class="form-control @error('gender') is-invalid @enderror"
                                            >
                                                <option value="">-- Select Gender --</option>
                                                @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                                    <option 
                                                        value="{{ $value }}" 
                                                        {{ old('gender', $admin->gender ?? '') == $value ? 'selected' : '' }}
                                                    >
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('gender')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Additional Details Section --}}
                                <h6 class="text-muted mb-3 mt-4">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    Additional Details
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                            <input
                                                type="date"
                                                name="dob"
                                                max="{{ now()->subYears(18)->format('Y-m-d') }}"
                                                id="dob"
                                                class="form-control @error('dob') is-invalid @enderror"
                                                value="{{ old('dob', isset($admin) && $admin->dob ? $admin->dob->format('Y-m-d') : '') }}"
                                            >
                                            @error('dob')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="emergency_contact" class="form-label">Emergency Contact <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">+91</span>
                                                </div>
                                                <input
                                                    type="tel"
                                                    name="emergency_contact"
                                                    id="emergency_contact"
                                                    maxlength="10"
                                                    class="form-control @error('emergency_contact') is-invalid @enderror"
                                                    value="{{ old('emergency_contact', $admin->emergency_contact ?? '') }}"
                                                >
                                            </div>
                                        </div>
                                        @error('emergency_contact')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                            <textarea
                                                name="address"
                                                id="address"
                                                class="form-control @error('address') is-invalid @enderror"
                                                rows="3"
                                                placeholder="Enter complete address"
                                            >{{ old('address', $admin->current_address ?? $admin->address ?? '') }}</textarea>
                                            @error('address')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Role Selection Section --}}
                                <h6 class="text-muted mb-3 mt-4">
                                    <i class="fas fa-user-tag mr-2"></i>
                                    Role Assignment
                                </h6>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="role_id" class="form-label">Assign Role <span class="text-danger">*</span></label>
                                            <select
                                                name="role_id"
                                                id="role_id"
                                                class="form-control @error('role_id') is-invalid @enderror"
                                            >
                                                <option value="">-- Select Role --</option>
                                                @foreach($roles as $role)
                                                    <option
                                                        value="{{ $role->id }}"
                                                        {{ old('role_id', isset($admin) ? $admin->user->role_id : '') == $role->id ? 'selected' : '' }}
                                                    >
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('role_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @else
                                                <div class="invalid-feedback">Please select a role for this admin.</div>
                                            @enderror
                                            <small class="form-text text-muted">Select the role that defines the permissions for this company admin.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('superadmin.assigned-company-admins.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to List
                            </a>
                            
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                id="submitBtn"
                                data-loading-text="Processing..."
                            >
                                <i class="fas fa-{{ isset($admin) ? 'save' : 'plus' }} mr-2"></i>
                                {{ isset($admin) ? 'Update' : 'Create' }} Company Admin
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Hidden Fields --}}
                @if(isset($admin))
                    <input type="hidden" name="user_id" value="{{ $admin->user_id }}">
                @endif
            </form>
        </div>
    </section>
</div>
@endsection

{{-- Additional Scripts Section --}}
@push('scripts')
{{-- @parent --}}
<script>
$(document).ready(function() {
    // Initialize company information display
    initializeCompanyInfoDisplay();
});

// Removed initializeFormValidation function


function initializeCompanyInfoDisplay() {
    const $companySelect = $('#company_id');
    const $companyInfoCard = $('#companyInfoCard');
    
    if ($companySelect.length === 0 || $companyInfoCard.length === 0) return;
    
    // Show initial selected company info on page load
    const selectedOption = $companySelect.find('option:selected')[0];
    if (selectedOption && selectedOption.value && selectedOption.dataset.companyData) {
        updateCompanyInfo(JSON.parse(selectedOption.dataset.companyData));
    }
    
    $companySelect.on('change', function() {
        const selectedOption = $(this).find('option:selected')[0];
        
        if (selectedOption && selectedOption.value && selectedOption.dataset.companyData) {
            const companyData = JSON.parse(selectedOption.dataset.companyData);
            updateCompanyInfo(companyData);
        } else {
            hideCompanyInfo();
        }
    });
}

function updateCompanyInfo(companyData) {
    const $companyInfoCard = $('#companyInfoCard');
    const $companyInfoTitle = $('#companyInfoTitle');
    const $companyStatusBadge = $('#companyStatusBadge');
    
    // Update title
    $companyInfoTitle.text(companyData.name);
    
    // Update status badge
    let statusClass = 'badge-success';
    let statusText = 'Available';
    
    if (companyData.adminCount > 0) {
        statusClass = 'badge-warning';
        statusText = `${companyData.adminCount} Admin${companyData.adminCount !== 1 ? 's' : ''} Assigned`;
    }
    
    $companyStatusBadge.removeClass().addClass(`badge ${statusClass}`).text(statusText);
    
    // Update company details
    $('#companyName').text(companyData.name || '-');
    $('#companyDomain').text(companyData.domain || '-');
    $('#companyEmail').text(companyData.email || '-');
    $('#companyPhone').text(companyData.phone || '-');
    $('#companyAddress').text(companyData.address || '-');
    
    // Update counts
    $('#employeeCount').text(companyData.employeeCount || 0);
    $('#adminCount').text(companyData.adminCount || 0);
    $('#userCount').text(companyData.userCount || 0);
    $('#departmentCount').text(companyData.departmentCount || 0);
    $('#designationCount').text(companyData.designationCount || 0);
    
    // Show the card
    $companyInfoCard.show().css('opacity', '0');
    
    // Add animation
    setTimeout(() => {
        $companyInfoCard.css('transition', 'opacity 0.3s ease').css('opacity', '1');
    }, 10);
}

function hideCompanyInfo() {
    const $companyInfoCard = $('#companyInfoCard');
    const $companyStatusBadge = $('#companyStatusBadge');
    
    // Reset status
    $companyStatusBadge.removeClass().addClass('badge badge-secondary').text('No Company Selected');
    
    // Hide with animation
    $companyInfoCard.css('transition', 'opacity 0.3s ease').css('opacity', '0');
    setTimeout(() => {
        $companyInfoCard.hide();
    }, 300);
}
</script>
@endpush
