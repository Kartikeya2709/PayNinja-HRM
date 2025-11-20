@extends('layouts.app')

@section('title', 'Submit Resignation Request')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Submit Resignation Request</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('resignations.index') }}">Resignations</a></div>
                <div class="breadcrumb-item">Submit Request</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Resignation Details</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('resignations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="form-group">
                                <label for="resignation_type" class="form-label">Resignation Type <span class="text-danger">*</span></label>
                                <select name="resignation_type" id="resignation_type" class="form-control @error('resignation_type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="voluntary" {{ old('resignation_type') == 'voluntary' ? 'selected' : '' }}>Voluntary Resignation</option>
                                    <option value="retirement" {{ old('resignation_type') == 'retirement' ? 'selected' : '' }}>Retirement</option>
                                    <option value="contract_end" {{ old('resignation_type') == 'contract_end' ? 'selected' : '' }}>Contract End</option>
                                </select>
                                @error('resignation_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="form-group">
                                <label for="resignation_date" class="form-label">Resignation Date <span class="text-danger">*</span></label>
                                <input type="date" name="resignation_date" id="resignation_date"
                                       class="form-control @error('resignation_date') is-invalid @enderror"
                                       value="{{ old('resignation_date', now()->format('Y-m-d')) }}"
                                       min="{{ now()->format('Y-m-d') }}" required>
                                @error('resignation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="form-group">
                                <label for="last_working_date" class="form-label">Last Working Date <span class="text-danger">*</span></label>
                                <input type="date" name="last_working_date" id="last_working_date"
                                       class="form-control @error('last_working_date') is-invalid @enderror"
                                       value="{{ old('last_working_date') }}"
                                       min="{{ now()->format('Y-m-d') }}" required>
                                @error('last_working_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="form-group">
                                <label for="notice_period_days" class="form-label">Notice Period (Days) <span class="text-danger">*</span></label>
                                <input type="number" name="notice_period_days" id="notice_period_days"
                                       class="form-control @error('notice_period_days') is-invalid @enderror"
                                       value="{{ old('notice_period_days', 30) }}"
                                       min="0" max="365" required readonly>
                                @error('notice_period_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Automatically calculated based on date difference</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="reason" class="form-label">Reason for Resignation <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="4"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="Please provide detailed reason for your resignation..."
                                  required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="attachment" class="form-label">Supporting Document (Optional)</label>
                        <input type="file" name="attachment" id="attachment"
                               class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Upload resignation letter or supporting documents (PDF, DOC, DOCX, JPG, PNG - Max 2MB)</small>
                    </div>

                    <div class="form-group">
                        <label for="employee_remarks" class="form-label">Additional Remarks (Optional)</label>
                        <textarea name="employee_remarks" id="employee_remarks" rows="3"
                                  class="form-control @error('employee_remarks') is-invalid @enderror"
                                  placeholder="Any additional information or remarks...">{{ old('employee_remarks') }}</textarea>
                        @error('employee_remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Important Information</h6>
                        <ul class="mb-0">
                            <li>Your resignation will be submitted to your reporting manager and HR for approval.</li>
                            <li>You will continue to have access to the system until your last working date.</li>
                            <li>During the notice period, you are expected to complete any pending tasks and handover responsibilities.</li>
                            <li>You can withdraw your resignation request if it's still in pending status.</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Resignation
                        </button>
                        <a href="{{ route('resignations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate notice period days when dates change
    $('#resignation_date, #last_working_date').on('change', function() {
        calculateNoticePeriod();
    });

    // Initial calculation
    calculateNoticePeriod();
});

function calculateNoticePeriod() {
    const resignationDate = $('#resignation_date').val();
    const lastWorkingDate = $('#last_working_date').val();

    if (resignationDate && lastWorkingDate) {
        const start = new Date(resignationDate);
        const end = new Date(lastWorkingDate);

        if (end >= start) {
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            $('#notice_period_days').val(diffDays);
        } else {
            $('#notice_period_days').val(0);
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Dates',
                text: 'Last working date must be after or equal to resignation date.'
            });
        }
    }
}

// Form validation
$('form').on('submit', function(e) {
    const resignationDate = $('#resignation_date').val();
    const lastWorkingDate = $('#last_working_date').val();

    if (resignationDate && lastWorkingDate) {
        const start = new Date(resignationDate);
        const end = new Date(lastWorkingDate);

        if (end < start) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Last working date must be after or equal to resignation date.'
            });
            return false;
        }
    }
});
</script>
@endpush