@extends('layouts.app')

@section('title', 'Create Leave Request')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css  " />
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px;
            padding: 8px 12px;
            border: 1px solid #dce4ec;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .btn-center {
            display: flex;
            justify-content: center;
        }
        .margin-bottom {
            margin-bottom: 1rem;
        }
    </style>
@endpush

@section('content')
<section class="section container">

    <div class="section-header">
        <h1>Create Leave Request</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('company.leave-requests.index') }}">Leave Requests</a></div>
            <div class="breadcrumb-item active">Create</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12 px-1">

                <!-- Leave Balance Display (Initially Hidden) -->
                <div class="card" id="leaveBalanceInfo">
                    <div class="text-center">
                        <h5>Leave Balances</h5>
                   
                    <div class="card-body">
                        <div class="row" id="leaveBalancesContainer">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="card">
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Total Days</th>
                                        <th>Used Days</th>
                                        <th>Remaining Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($leaveBalances->count() > 0)
                                        @foreach($leaveBalances as $balance)
                                            @php
                                                $leaveTypeName = $balance->leaveType->name ?? 'N/A';
                                                $remainingDays = $balance->remaining_days;
                                            @endphp
                                            <tr>
                                                <td>{{ $leaveTypeName }}</td>
                                                <td>{{ $balance->total_days }}</td>
                                                <td>{{ $balance->used_days }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $remainingDays > 0 ? 'success' : 'danger' }}">
                                                        {{ $remainingDays }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No leave balances found for the current year</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card pt-0">
                    <div class="card-header justify-content-center mb-3 btn-center margin-bottom">
                        <h5>Leave Request Form</h5>
                    </div>
                    <div class="card-body">

                        <form method="POST" action="{{ route('company.leave-requests.store') }}" enctype="multipart/form-data" id="leaveRequestForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" value="{{ $currentEmployee->name }} ({{ $currentEmployee->employee_code ?? 'N/A' }})" readonly>
                                        <input type="hidden" name="employee_id" value="{{ $currentEmployee->id }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                                        <select id="leave_type_id" name="leave_type_id" class="form-control select2 @error('leave_type_id') is-invalid @enderror" required>
                                            <option value="">Select leave type</option>
                                            @foreach($leaveTypes as $type)
                                                @php
                                                    $balance = $leaveBalances->firstWhere('leave_type_id', $type->id);
                                                    $remainingDays = $balance ? $balance->remaining_days : 0;
                                                @endphp
                                                <option value="{{ $type->id }}" data-remaining="{{ $remainingDays }}">
                                                    {{ $type->name }} (Remaining: {{ $remainingDays }} days)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('leave_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" id="start_date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="end_date">End Date <span class="text-danger">*</span></label>
                                        <input type="date" id="end_date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" required>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="reason">Reason <span class="text-danger">*</span></label>
                                <textarea id="reason" name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" required placeholder="Please provide a detailed reason for your leave request (minimum 10 characters)">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <h6>Please fix the following errors:</h6>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group text-center">
                                <a href="{{ route('company.leave-requests.index') }}" class="btn btn-link btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select leave type',
            allowClear: true
        });

        // Form validation
        $('#leaveRequestForm').on('submit', function(e) {
            let isValid = true;
            const $form = $(this);
            
            // Reset error states
            $form.find('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            
            // Validate leave type
            if (!$('#leave_type_id').val()) {
                showError('leave_type_id', 'Please select a leave type');
                isValid = false;
            }
            
            // Validate start date
            const startDate = new Date($('#start_date').val());
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (!$('#start_date').val()) {
                showError('start_date', 'Please select a start date');
                isValid = false;
            } else if (startDate < today) {
                showError('start_date', 'Start date cannot be in the past');
                isValid = false;
            }
            
            // Validate end date
            if (!$('#end_date').val()) {
                showError('end_date', 'Please select an end date');
                isValid = false;
            } else if ($('#start_date').val() && new Date($('#end_date').val()) < new Date($('#start_date').val())) {
                showError('end_date', 'End date cannot be before start date');
                isValid = false;
            }
            
            // Validate reason
            if ($.trim($('#reason').val()).length < 10) {
                showError('reason', 'Reason must be at least 10 characters long');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('.alert-danger').first().offset().top - 100
                }, 500);
            }
        });
        
        function showError(field, message) {
            const $field = $('#' + field);
            $field.addClass('is-invalid');
            $field.after('<div class="invalid-feedback d-block">' + message + '</div>');
        }

        // Toggle half day type selection
        $('#is_half_day').change(function() {
            if ($(this).is(':checked')) {
                $('#halfDayTypeContainer').slideDown();
                $('#end_date').val($('#start_date').val());
            } else {
                $('#halfDayTypeContainer').slideUp();
            }
        });

        // Update end date when start date changes for half day leave
        $('#start_date').change(function() {
            if ($('#is_half_day').is(':checked')) {
                $('#end_date').val($(this).val());
            }
            validateDates();
        });

        // Validate end date is not before start date
        $('#end_date').change(function() {
            validateDates();
        });

        // Function to validate dates
        function validateDates() {
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($('#end_date').val());

            if (startDate && endDate && endDate < startDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'End date cannot be before start date',
                });
                $('#end_date').val($('#start_date').val());
            }
        }

        // Handle file input change
        $('#attachment').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Choose file');

            // Validate file size (5MB max)
            const fileSize = this.files[0] ? this.files[0].size / 1024 / 1024 : 0; // in MB
            if (fileSize > 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'File too large',
                    text: 'Maximum file size is 5MB',
                });
                $(this).val('');
            }
        });


        // Form submission handling
        $('#leaveRequestForm').on('submit', function(e) {
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($('#end_date').val());

            if (startDate && endDate && endDate < startDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'End date cannot be before start date',
                });
                return false;
            }

            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
        });
    });
</script>
@endpush
