@extends('layouts.app')

@section('title', 'Edit Resignation Request')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Edit Resignation Request</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('employee.resignations.index') }}">Resignations</a></div>
                <div class="breadcrumb-item">Edit Request</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Edit Resignation Details</h4>
                <div class="card-header-action">
                    <span class="badge badge-{{ $resignation->status_color }}">
                        {{ $resignation->status_label }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <form action="{{ route('employee.resignations.update', $resignation) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="resignation_type" class="form-label">Resignation Type <span class="text-danger">*</span></label>
                                <select name="resignation_type" id="resignation_type" class="form-control @error('resignation_type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="voluntary" {{ old('resignation_type', $resignation->resignation_type) == 'voluntary' ? 'selected' : '' }}>Voluntary Resignation</option>
                                    <option value="retirement" {{ old('resignation_type', $resignation->resignation_type) == 'retirement' ? 'selected' : '' }}>Retirement</option>
                                    <option value="contract_end" {{ old('resignation_type', $resignation->resignation_type) == 'contract_end' ? 'selected' : '' }}>Contract End</option>
                                </select>
                                @error('resignation_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="resignation_date" class="form-label">Resignation Date <span class="text-danger">*</span></label>
                                <input type="date" name="resignation_date" id="resignation_date"
                                       class="form-control @error('resignation_date') is-invalid @enderror"
                                       value="{{ old('resignation_date', $resignation->resignation_date->format('Y-m-d')) }}"
                                       min="{{ now()->format('Y-m-d') }}" required>
                                @error('resignation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_working_date" class="form-label">Last Working Date <span class="text-danger">*</span></label>
                                <input type="date" name="last_working_date" id="last_working_date"
                                       class="form-control @error('last_working_date') is-invalid @enderror"
                                       value="{{ old('last_working_date', $resignation->last_working_date->format('Y-m-d')) }}"
                                       min="{{ now()->format('Y-m-d') }}" required>
                                @error('last_working_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notice_period_days" class="form-label">Notice Period (Days) <span class="text-danger">*</span></label>
                                <input type="number" name="notice_period_days" id="notice_period_days"
                                       class="form-control @error('notice_period_days') is-invalid @enderror"
                                       value="{{ old('notice_period_days', $resignation->notice_period_days) }}"
                                       min="0" max="365" required readonly>
                                @error('notice_period_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Automatically calculated based on date difference</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reason" class="form-label">Reason for Resignation <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="4"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="Please provide detailed reason for your resignation..."
                                  required>{{ old('reason', $resignation->reason) }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="attachment" class="form-label">Supporting Document (Optional)</label>
                        @if($resignation->attachment_path)
                            <div class="mb-2">
                                <small class="text-muted">Current file: </small>
                                <a href="{{ Storage::url($resignation->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-download"></i> View Current Document
                                </a>
                            </div>
                        @endif
                        <input type="file" name="attachment" id="attachment"
                               class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Upload new resignation letter or supporting documents (PDF, DOC, DOCX, JPG, PNG - Max 2MB)
                            @if($resignation->attachment_path)
                                <br><strong>Note:</strong> Uploading a new file will replace the existing document.
                            @endif
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="employee_remarks" class="form-label">Additional Remarks (Optional)</label>
                        <textarea name="employee_remarks" id="employee_remarks" rows="3"
                                  class="form-control @error('employee_remarks') is-invalid @enderror"
                                  placeholder="Any additional information or remarks...">{{ old('employee_remarks', $resignation->employee_remarks) }}</textarea>
                        @error('employee_remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Important Notes</h6>
                        <ul class="mb-0">
                            <li>You can only edit resignation requests that are in <strong>pending</strong> status.</li>
                            <li>Once approved by HR or management, the resignation cannot be modified.</li>
                            <li>Any changes will reset the approval process.</li>
                            <li>Make sure all information is accurate before saving.</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Resignation
                        </button>
                        <a href="{{ route('employee.resignations.show', $resignation) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <a href="{{ route('employee.resignations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current Resignation Information -->
        <div class="card">
            <div class="card-header">
                <h4>Current Resignation Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Current Type</label>
                            <p class="form-control-plaintext">
                                <span class="badge badge-info">{{ $resignation->resignation_type_label }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Current Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge badge-{{ $resignation->status_color }}">
                                    {{ $resignation->status_label }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Submitted On</label>
                            <p class="form-control-plaintext">{{ $resignation->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Last Updated</label>
                            <p class="form-control-plaintext">{{ $resignation->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
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

    // Confirm update
    e.preventDefault();
    Swal.fire({
        title: 'Update Resignation?',
        text: "Are you sure you want to update this resignation request?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form
            e.target.submit();
        }
    });
});
</script>
@endpush