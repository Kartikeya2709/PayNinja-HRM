@extends('layouts.app')

@section('title', 'Generate Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Generate New Payroll</h3>
                    <a href="{{ route('admin.payroll.index') }}" class="btn btn-secondary btn-sm">Back to Payrolls</a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info">{{ session('info') }}</div>
                    @endif

                    @if($employees->isEmpty() && !(Auth::user()->hasRole('superadmin') && $employees->isEmpty()))
                        <div class="alert alert-warning">
                            @if(Auth::user()->hasRole('superadmin'))
                                No active employees found in the system. Please add employees before generating payroll.
                            @else
                                No active employees found in your company. Please ensure employees are added and active.
                            @endif
                        </div>
                    @else
                        <ul class="nav nav-tabs mb-4 navbars-payrolls" id="payrollTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab" aria-controls="single" aria-selected="true">Single Employee</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab" aria-controls="bulk" aria-selected="false">All Employees</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="payrollTabsContent">
                            <!-- Single Employee Tab -->
                            <div class="tab-pane fade show active" id="single" role="tabpanel" aria-labelledby="single-tab">
                                <form action="{{ route('admin.payroll.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="payroll_type" value="single">

                                    <div class="form-group mb-3">
                                        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                                        <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                                            <option value="">Select Employee</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->name }} ({{ $employee->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('employee_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="pay_period_start" class="form-label">Pay Period Start <span class="text-danger">*</span></label>
                                                <input type="date" name="pay_period_start" id="pay_period_start" class="form-control @error('pay_period_start') is-invalid @enderror" value="{{ old('pay_period_start') }}" required>
                                                @error('pay_period_start')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="pay_period_end" class="form-label">Pay Period End <span class="text-danger">*</span></label>
                                                <input type="date" name="pay_period_end" id="pay_period_end" class="form-control @error('pay_period_end') is-invalid @enderror" value="{{ old('pay_period_end') }}" required>
                                                @error('pay_period_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">Generate Payroll</button>
                                        <a href="{{ route('admin.payroll.index') }}" class="btn btn-link btn-danger">Cancel</a>
                                    </div>
                                </form>
                            </div>

                            <!-- All Employees Tab -->
                            <div class="tab-pane fade" id="bulk" role="tabpanel" aria-labelledby="bulk-tab">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    This will generate payroll for all active employees in your company for the specified pay period.
                                </div>

                                <form action="{{ route('admin.payroll.store') }}" method="POST" id="bulk-payroll-form">
                                    @csrf
                                    <input type="hidden" name="payroll_type" value="bulk">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="bulk_pay_period_start" class="form-label">Pay Period Start <span class="text-danger">*</span></label>
                                                <input type="date" name="pay_period_start" id="bulk_pay_period_start" class="form-control @error('pay_period_start') is-invalid @enderror" value="{{ old('pay_period_start') }}" required>
                                                @error('pay_period_start')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="bulk_pay_period_end" class="form-label">Pay Period End <span class="text-danger">*</span></label>
                                                <input type="date" name="pay_period_end" id="bulk_pay_period_end" class="form-control @error('pay_period_end') is-invalid @enderror" value="{{ old('pay_period_end') }}" required>
                                                @error('pay_period_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="skip_processed" id="skip_processed" value="1" checked>
                                        <label class="form-check-label" for="skip_processed">
                                            Skip employees with existing payroll for this period
                                        </label>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        This action will process payroll for all active employees. It may take several minutes to complete.
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary" id="process-bulk-payroll">
                                            <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                                            Generate Payroll for All Employees
                                        </button>
                                        <a href="{{ route('admin.payroll.index') }}" class="btn btn-link btn-danger">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date pickers with default values if not set
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        const formatDate = (date) => {
            return date.toISOString().split('T')[0];
        };

        // Set default dates if not already set
        if (!document.getElementById('pay_period_start').value) {
            document.getElementById('pay_period_start').value = formatDate(firstDay);
        }
        if (!document.getElementById('pay_period_end').value) {
            document.getElementById('pay_period_end').value = formatDate(today);
        }
        if (!document.getElementById('bulk_pay_period_start').value) {
            document.getElementById('bulk_pay_period_start').value = formatDate(firstDay);
        }
        if (!document.getElementById('bulk_pay_period_end').value) {
            document.getElementById('bulk_pay_period_end').value = formatDate(today);
        }

        // Handle bulk form submission
        const bulkForm = document.getElementById('bulk-payroll-form');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                const submitBtn = document.querySelector('#process-bulk-payroll');
                const spinner = submitBtn.querySelector('.spinner-border');
                
                // Show loading state
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                
                // Optional: Show a confirmation dialog
                if (!confirm('Are you sure you want to generate payroll for all employees? This action cannot be undone.')) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                }
            });
        }
    });
</script>
@endpush
