@extends('layouts.app')

@section('title', 'Payroll Settings - ' . $company->name)

@section('content')
<div class="main-content container payroll-setting">
    <section class="section">
        <div class="section-header">
            <h1>Payroll Settings</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Payroll Settings</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Payroll Settings for {{ $company->name }}</h4>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible show fade">
                                    <div class="alert-body">
                                        <button class="close" data-dismiss="alert">
                                            <span>&times;</span>
                                        </button>
                                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                                    </div>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible show fade">
                                    <div class="alert-body">
                                        <button class="close" data-dismiss="alert">
                                            <span>&times;</span>
                                        </button>
                                        <i class="fas fa-exclamation-circle mr-2"></i> 
                                        <strong>Please correct the following errors:</strong>
                                        <ul class="mt-2 mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.payroll.settings.update') }}" class="needs-validation" novalidate="">
                                @csrf
                                @method('PUT')

                                <div class="form-group row align-items-center">
                                    <label for="deductible_leave_type_ids" class="col-sm-3 col-form-label">Deductible Leave Types</label>
                                    <div class="col-sm-9">
                                        <select multiple class="form-control @error('deductible_leave_type_ids') is-invalid @enderror" 
                                                id="deductible_leave_type_ids" 
                                                name="deductible_leave_type_ids[]" 
                                                style="height: auto; min-height: 38px;">
                                            <option value="" disabled>Select leave types</option>
                                            @foreach($leaveTypes as $leaveType)
                                                <option value="{{ $leaveType->id }}" {{ in_array($leaveType->id, old('deductible_leave_type_ids', $settings->deductible_leave_type_ids ?? [])) ? 'selected' : '' }}>
                                                    {{ $leaveType->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Select leave types for which salary should be deducted if taken.</small>
                                        @error('deductible_leave_type_ids')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label for="late_arrival_threshold" class="col-sm-3 col-form-label">Late Arrival Threshold</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="number" 
                                                   class="form-control @error('late_arrival_threshold') is-invalid @enderror" 
                                                   id="late_arrival_threshold" 
                                                   name="late_arrival_threshold" 
                                                   value="{{ old('late_arrival_threshold', $settings->late_arrival_threshold) }}" 
                                                   min="0"
                                                   placeholder="e.g. 3">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    times
                                                </div>
                                            </div>
                                            @error('late_arrival_threshold')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted">Number of late arrivals after which salary deduction applies. Leave blank or set to 0 to disable.</small>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label for="late_arrival_deduction_days" class="col-sm-3 col-form-label">Late Arrival Deduction</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="number" 
                                                   step="0.01" 
                                                   class="form-control @error('late_arrival_deduction_days') is-invalid @enderror" 
                                                   id="late_arrival_deduction_days" 
                                                   name="late_arrival_deduction_days" 
                                                   value="{{ old('late_arrival_deduction_days', $settings->late_arrival_deduction_days) }}" 
                                                   min="0"
                                                   placeholder="e.g. 0.5">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    days
                                                </div>
                                            </div>
                                            @error('late_arrival_deduction_days')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted">Number of days' salary to deduct if late arrival threshold is met (e.g., 0.5 for half day).</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row align-items-center">
                                    <label for="days_in_month" class="col-sm-3 col-form-label">Days in Month for Payroll</label>
                                    <div class="col-sm-9">
                                        <input type="number" 
                                               class="form-control @error('days_in_month') is-invalid @enderror" 
                                               id="days_in_month" 
                                               name="days_in_month" 
                                               value="{{ old('days_in_month', $settings->days_in_month ?? 30) }}" 
                                               min="1" 
                                               max="31"
                                               required>
                                        <small class="form-text text-muted">Number of days in a month used for calculating daily rates (default: 30).</small>
                                        @error('days_in_month')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                

                                <div class="form-group row">
                                    <div class="col-sm-9 offset-sm-3">
                                        <button type="submit" class="btn btn-primary btn-icon icon-left">
                                            <i class="fas fa-save"></i> Save Settings
                                        </button>
                                        <a href="{{ route('home') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                                        </a>
                                    </div>
                                </div>
                            </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <!-- Custom CSS for this page -->
    <style>
        .card {
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
            border: none;
            border-radius: 8px;
        }
        .card-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e3e6f0;
            padding: 1.25rem 1.5rem;
        }
        .card-header h4 {
            font-weight: 600;
            color: #34395e;
            margin: 0;
        }
        .section-header {
            margin-bottom: 2rem;
        }
        .section-header h1 {
            font-size: 1.5rem;
            color: #34395e;
            font-weight: 700;
        }
        .form-control:focus {
            border-color: #6777ef;
            box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.25);
        }
        .btn-primary {
            background-color: #6777ef;
            border-color: #6777ef;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #4f5fcf;
            border-color: #4f5fcf;
        }
        .btn-icon {
            padding: 0.5rem 1rem 0.5rem 1rem;
        }
        .btn-icon i {
            margin-right: 0.5rem;
        }
        .invalid-feedback {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        .form-text {
            color: #6c757d;
            font-size: 0.8125rem;
            margin-top: 0.25rem;
        }
        .alert {
            border: none;
            border-radius: 0.25rem;
        }
        .alert-success {
            background-color: #e3f7ee;
            color: #1f9d55;
        }
        .alert-danger {
            background-color: #fce4e4;
            color: #e74c3c;
        }
        .alert .close {
            padding: 1.25rem 1.5rem;
            opacity: 1;
        }
        .alert .alert-body {
            padding: 1rem 1.5rem;
        }
        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }
        .breadcrumb-item.active {
            color: #34395e;
            font-weight: 600;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Enable Bootstrap 4 validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        Deductible Leave Types    </script>
@endpush
