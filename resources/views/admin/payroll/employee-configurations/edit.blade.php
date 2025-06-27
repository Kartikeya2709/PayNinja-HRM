@extends('layouts.app')

@section('title', 'Edit Employee Payroll Configuration')

@section('content')
@push('styles')
<style>
.status-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
}

.status-toggle .badge {
    font-size: 0.85rem;
    padding: 0.4em 0.8em;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.status-toggle .btn {
    line-height: 1.2;
}

.status-toggle .btn i {
    margin-right: 4px;
}
</style>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to calculate other components based on basic salary
        function updateSalaryComponents() {
            const basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
            // const hra = basicSalary * 0.5; // 50% of basic
            // const da = basicSalary * 0.2;  // 20% of basic
            
            // Update the display of calculated values
            // document.getElementById('hra-display').textContent = hra.toFixed(2);
            // document.getElementById('da-display').textContent = da.toFixed(2);
        }

        // Function to toggle badge fields based on checkbox state
        function toggleBadgeFields(checkbox) {
            const card = checkbox.closest('.card');
            const inputs = card.querySelectorAll('input, select');
            const isChecked = checkbox.checked;
            
            inputs.forEach(input => {
                if (input !== checkbox) {
                    input.disabled = !isChecked;
                    
                    // Special handling for based-on select
                    if (input.classList.contains('based-on-select')) {
                        const calculationType = card.querySelector('.calculation-type-select').value;
                        input.disabled = !isChecked || calculationType !== 'percentage';
                    }
                }
            });
        }

        // Handle calculation type change
        function handleCalculationTypeChange(select) {
            const card = select.closest('.card');
            const basedOnSelect = card.querySelector('.based-on-select');
            const isPercentage = select.value === 'percentage';
            
            basedOnSelect.disabled = !isPercentage;
        }

        // Add event listeners
        const basicSalaryInput = document.getElementById('basic_salary');
        if (basicSalaryInput) {
            basicSalaryInput.addEventListener('input', updateSalaryComponents);
            updateSalaryComponents();
        }

        // Initialize badge toggles
        document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(checkbox => {
            // Set initial state
            toggleBadgeFields(checkbox);
            
            // Add change event
            checkbox.addEventListener('change', function() {
                toggleBadgeFields(this);
            });
        });

        // Initialize calculation type changes
        document.querySelectorAll('.calculation-type-select').forEach(select => {
            // Set initial state
            handleCalculationTypeChange(select);
            
            // Add change event
            select.addEventListener('change', function() {
                handleCalculationTypeChange(this);
            });
        });
    });
</script>
@endpush

<section class="section">
    <div class="section-header">
        <h1>Edit Payroll Configuration for {{ $employee->user->name }}</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-header">
                <h4>Employee Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Employee ID:</strong> {{ $employee->employee_id }}</p>
                        <p><strong>Name:</strong> {{ $employee->user->name }}</p>
                        <p><strong>Email:</strong> {{ $employee->user->email }}</p>
                        <p><strong>Department:</strong> {{ $employee->department->name ?? 'N/A' }}</p>
                        <p><strong>Designation:</strong> {{ $employee->designation->title ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Joining Date:</strong> {{ $employee->joining_date->format('d M, Y') }}</p>
                        <p><strong>Status:</strong> {!! $employee->status == 'active' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' !!}</p>
                        @if($currentSalary && $currentSalary->ctc)
                            <div class="mt-3 p-3 bg-light rounded">
                                <h5>Current Salary Details</h5>
                                <p class="mb-1">
                                    <strong>CTC:</strong> 
                                    <span class="font-weight-bold text-primary">
                                        {{ $employee->company->default_currency ?? '₹' }} {{ number_format($currentSalary->ctc, 2) }}
                                    </span>
                                </p>
                                <p class="mb-1">
                                    <strong>Basic Salary:</strong> 
                                    <span class="font-weight-bold">
                                        {{ $employee->company->default_currency ?? '₹' }} {{ number_format($currentSalary->basic_salary, 2) }}
                                    </span>
                                </p>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-calendar-alt"></i> 
                                    Effective from: {{ $currentSalary->effective_from ? $currentSalary->effective_from->format('d M, Y') : 'N/A' }}
                                </small>
                            </div>
                        @else
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle"></i> No salary information available.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Employee Salary Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ $employee->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Current CTC:</strong> {{ $currentSalary->ctc ? number_format($currentSalary->ctc, 2) : 'Not set' }}</p>
                        <p><strong>Basic Salary:</strong> {{ $currentSalary->basic_salary ? number_format($currentSalary->basic_salary, 2) : 'Not set' }}</p>
                        <!--<p><strong>HRA:</strong> {{ $currentSalary->hra ? number_format($currentSalary->hra, 2) : 'Not set' }}</p>
                        <p><strong>DA:</strong> {{ $currentSalary->da ? number_format($currentSalary->da, 2) : 'Not set' }}</p>-->
                        <div class="status-toggle">
                            <form id="statusForm" action="{{ route('admin.payroll.employee-configurations.set-current', ['employee' => $employee->id, 'employeeSalary' => $currentSalary->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group mb-0">
                                    <label class="d-flex align-items-center mb-0">
                                        <strong class="mr-3">Current Status:</strong>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="isCurrentToggle" 
                                                   name="is_current" value="1" 
                                                   {{ $currentSalary->is_current ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="isCurrentToggle">
                                                @if($currentSalary->is_current)
                                                    <span class="text-success"><i class="fas fa-check-circle"></i> Active</span>
                                                @else
                                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                                                @endif
                                            </label>
                                        </div>
                                    </label>
                                    <small class="form-text text-muted">
                                        Toggle to set this as the current active salary. Only one salary can be active at a time.
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTC and Salary Update Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Salary Configuration</h4>
            </div>
            <div class="card-body">
                <form id="salaryUpdateForm" action="{{ route('admin.employee-payroll-configurations.update-salary', $employee) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ctc">Cost to Company (CTC)</label>
                                <input type="number" name="ctc" id="ctc" class="form-control" 
                                       value="{{ old('ctc', $currentSalary->ctc) }}" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="basic_salary">Basic Salary</label>
                                <input type="number" name="basic_salary" id="basic_salary" class="form-control" 
                                       value="{{ old('basic_salary', $currentSalary->basic_salary) }}" 
                                       step="0.01" min="0" required>
                                <small class="form-text text-muted">
                                    <!--HRA (50% of basic): <span id="hra-display">{{ number_format(($currentSalary->basic_salary ?? 0) * 0.5, 2) }}</span> | 
                                    DA (20% of basic): <span id="da-display">{{ number_format(($currentSalary->basic_salary ?? 0) * 0.2, 2) }}</span>-->
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-primary" id="saveSalaryBtn">
                            <i class="fas fa-save mr-1"></i> Save Salary Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Beneficiary Badges Form -->
        <div class="card">
            <form action="{{ route('admin.employee-payroll-configurations.update', $employee) }}" method="POST" id="beneficiaryBadgesForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="_method" value="PUT">

                <div class="card-header">
                    <h4>Beneficiary Badges</h4>
                </div>
                <div class="card-body">

                    <hr>
                    <h5>Beneficiary Badges</h5>
                    
                    @if($availableBadges->isEmpty())
                        <div class="alert alert-info">No beneficiary badges found for your company.</div>
                    @else
                        <div class="row">
                            @foreach($availableBadges as $badge)
                                @php
                                    $assignedBadge = $assignedBadgesMap->get($badge->id);
                                    $isApplicable = $assignedBadge ? $assignedBadge->is_applicable : false;
                                    
                                    // Get values from assigned badge or fall back to default badge values
                                    $value = $assignedBadge->custom_value ?? $assignedBadge->beneficiaryBadge->value ?? 0;
                                    $calculationType = $assignedBadge->custom_calculation_type ?? $assignedBadge->beneficiaryBadge->calculation_type ?? 'flat';
                                    $basedOn = $assignedBadge->custom_based_on ?? $assignedBadge->beneficiaryBadge->based_on ?? '';
                                    
                                    // Format the value for display
                                    $displayValue = '';
                                    if ($isApplicable) {
                                        $displayValue = $calculationType === 'percentage' 
                                            ? number_format($value, 2) . '% of ' . ucfirst(str_replace('_', ' ', $basedOn))
                                            : number_format($value, 2);
                                    }
                                @endphp
                                @if($isApplicable)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">{{ $badge->name }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Status:</span>
                                                    <span class="badge bg-success">Active</span>
                                                </div>
                                                @if($isApplicable && $displayValue)
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Value:</span>
                                                        <span class="fw-bold">{{ $displayValue }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted">Type:</span>
                                                        <span class="text-uppercase">{{ $calculationType }}</span>
                                                    </div>
                                                @else
                                                    <div class="text-muted">Not configured</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">Save Configuration</button>
                    <a href="{{ route('admin.employee-payroll-configurations.index') }}" class="btn btn-secondary">Cancel</a>
                </div> -->
            </form>
        </div>
    </div>
</section>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update HRA and DA display when basic salary changes
    const basicSalaryInput = document.getElementById('basic_salary');
    const hraDisplay = document.getElementById('hra-display');
    const daDisplay = document.getElementById('da-display');

    function updateSalaryCalculations() {
        const basicSalary = parseFloat(basicSalaryInput.value) || 0;
        const hra = basicSalary * 0.5;
        const da = basicSalary * 0.2;
        
        hraDisplay.textContent = hra.toFixed(2);
        daDisplay.textContent = da.toFixed(2);
    }

    if (basicSalaryInput) {
        basicSalaryInput.addEventListener('input', updateSalaryCalculations);
        // Initial calculation
        updateSalaryCalculations();
    }

    // Handle salary form submission
    const salaryForm = document.getElementById('salaryUpdateForm');
    if (salaryForm) {
        salaryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(salaryForm);
            const saveBtn = document.getElementById('saveSalaryBtn');
            const originalBtnText = saveBtn.innerHTML;
            
            // Disable button and show loading state
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
            
            fetch(salaryForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successAlert = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            ${data.message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>`;
                    
                    // Insert alert before the form
                    salaryForm.insertAdjacentHTML('beforebegin', successAlert);
                    
                    // Scroll to the top of the form
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    // Optional: Update any other parts of the page with the new data
                    if (data.ctc) {
                        document.getElementById('ctc').value = parseFloat(data.ctc).toFixed(2);
                    }
                    if (data.basic_salary) {
                        document.getElementById('basic_salary').value = parseFloat(data.basic_salary).toFixed(2);
                        updateSalaryCalculations();
                    }
                } else {
                    throw new Error(data.message || 'Failed to update salary');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message
                const errorAlert = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        ${error.message || 'An error occurred while updating the salary'}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>`;
                
                // Insert alert before the form
                salaryForm.insertAdjacentHTML('beforebegin', errorAlert);
                
                // Scroll to the top of the form
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .finally(() => {
                // Re-enable button and restore text
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnText;
                
                // Auto-dismiss alerts after 5 seconds
                setTimeout(() => {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(alert => {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });
        });
    }
    const toggleSwitch = document.getElementById('isCurrentToggle');
    const statusForm = document.getElementById('statusForm');
    const statusLabel = document.querySelector('label[for="isCurrentToggle"] .custom-control-label');
    
    if (toggleSwitch && statusForm) {
        toggleSwitch.addEventListener('change', function() {
            // Update the UI immediately for better UX
            if (this.checked) {
                statusLabel.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Active</span>';
            } else {
                statusLabel.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Inactive</span>';
            }
            
            // Show loading state
            const originalText = statusLabel.innerHTML;
            statusLabel.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            
            // Submit the form via AJAX
            const formData = new URLSearchParams();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('_method', 'PUT');
            formData.append('is_current', this.checked ? '1' : '0');
            
            // Submit the form
            fetch(statusForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show mt-3';
                    alert.role = 'alert';
                    alert.innerHTML = `
                        <i class="fas fa-check-circle"></i> ${data.message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    `;
                    statusForm.parentNode.insertBefore(alert, statusForm.nextSibling);
                    
                    // Remove the alert after 3 seconds
                    setTimeout(() => {
                        alert.remove();
                    }, 3000);
                } else {
                    throw new Error(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert the toggle if there was an error
                toggleSwitch.checked = !toggleSwitch.checked;
                statusLabel.innerHTML = toggleSwitch.checked 
                    ? '<span class="text-success"><i class="fas fa-check-circle"></i> Active</span>'
                    : '<span class="text-danger"><i class="fas fa-times-circle"></i> Inactive</span>';
                
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
                alert.role = 'alert';
                alert.innerHTML = `
                    <i class="fas fa-exclamation-circle"></i> ${error.message || 'Failed to update status. Please try again.'}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                `;
                statusForm.parentNode.insertBefore(alert, statusForm.nextSibling);
                
                // Remove the alert after 5 seconds
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            });
        });
    }
});
</script>
@endpush

@endsection
