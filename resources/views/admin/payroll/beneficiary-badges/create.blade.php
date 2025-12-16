@extends('layouts.app')

@section('title', 'Create New Beneficiary Badge')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>New Beneficiary Badge</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="#">New Beneficiary Badge</a></div>
            </div>
        </div>
    <div class="row">
      
        <div class="col-lg-12">
              <div class="card">
            
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Create New Beneficiary Badge</h5>
                     <a href="{{ route('admin.payroll.beneficiary-badges.index') }}" class="btn btn-secondary add-list"><i class="fa-solid fa-arrow-left me-2"></i>Back to List</a>
             
                </div>
               
           
                   <p class="mb-3">Define a new allowance or deduction badge for payroll.</p>
        

      
                <div class="card-body">
                    <form action="{{ route('admin.payroll.beneficiary-badges.store') }}" method="POST" id="beneficiaryBadgeForm">
                        @csrf
                        
                        @include('admin.payroll.beneficiary-badges._form')

                        <div class="mt-2 text-center">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status" aria-hidden="true"></span>
                                Create Badge
                            </button>
                            <a href="{{ route('admin.payroll.beneficiary-badges.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('beneficiaryBadgeForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');
    const companyWideCheckbox = document.getElementById('is_company_wide');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            submitSpinner.classList.remove('d-none');
            
            // Check if this is a company-wide badge
            if (companyWideCheckbox && companyWideCheckbox.checked) {
                const badgeName = document.getElementById('name').value;
                const badgeType = document.getElementById('type').value;
                
                e.preventDefault(); // Prevent form submission
                
                // Show confirmation dialog
                if (confirm(`This badge "${badgeName}" will be applied to ALL employees in your company. This action cannot be undone.\\n\\nDo you want to continue?`)) {
                    // Re-submit the form after confirmation
                    form.submit();
                } else {
                    // Reset button state if user cancels
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('d-none');
                }
            }
        });
    }
    
    // Form validation enhancement
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(function(field) {
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
});
</script>
@endPushOnce

@endsection
