@extends('layouts.app')
@section('title', 'Edit Field Visit')

@section('content')
<div class="main-content-01 container">
    <section class="section">
        <div class="section-header">
            <h1>Edit Field Visit</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('field-visits.index') }}">Field Visits</a></div>
                <div class="breadcrumb-item"><a href="{{ route('field-visits.show', $fieldVisit) }}">{{ $fieldVisit->visit_title }}</a></div>
                <div class="breadcrumb-item active">Edit</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header justify-content-center">
                            <h4>Visit Details</h4>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('field-visits.update', $fieldVisit) }}" method="POST" id="fieldVisitForm">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <div class="form-group">
                                            <label for="reporting_manager">Reporting Manager</label>
                                            <input type="text" class="form-control"
                                                   value="{{ $fieldVisit->reportingManager->name ?? 'No Reporting Manager' }}" readonly>
                                            <small class="form-text text-muted">From your employee profile</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="visit_title">Visit Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('visit_title') is-invalid @enderror"
                                                   id="visit_title" name="visit_title"
                                                   value="{{ old('visit_title', $fieldVisit->visit_title) }}" required>
                                            @error('visit_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                

                          
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="visit_description">Visit Description</label>
                                            <textarea class="form-control @error('visit_description') is-invalid @enderror"
                                                      id="visit_description" name="visit_description" rows="3">{{ old('visit_description', $fieldVisit->visit_description) }}</textarea>
                                            @error('visit_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="location_name">Location Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('location_name') is-invalid @enderror"
                                                   id="location_name" name="location_name"
                                                   value="{{ old('location_name', $fieldVisit->location_name) }}" required>
                                            @error('location_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="location_address">Location Address <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('location_address') is-invalid @enderror"
                                                   id="location_address" name="location_address"
                                                   value="{{ old('location_address', $fieldVisit->location_address) }}" required>
                                            @error('location_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="latitude">Latitude</label>
                                            <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror"
                                                   id="latitude" name="latitude"
                                                   value="{{ old('latitude', $fieldVisit->latitude) }}"
                                                   min="-90" max="90" placeholder="Optional">
                                            @error('latitude')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="longitude">Longitude</label>
                                            <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror"
                                                   id="longitude" name="longitude"
                                                   value="{{ old('longitude', $fieldVisit->longitude) }}"
                                                   min="-180" max="180" placeholder="Optional">
                                            @error('longitude')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="scheduled_start_datetime">Start Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('scheduled_start_datetime') is-invalid @enderror"
                                                   id="scheduled_start_datetime" name="scheduled_start_datetime"
                                                   value="{{ old('scheduled_start_datetime', $fieldVisit->scheduled_start_datetime->format('Y-m-d\TH:i')) }}" required>
                                            @error('scheduled_start_datetime')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="scheduled_end_datetime">End Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('scheduled_end_datetime') is-invalid @enderror"
                                                   id="scheduled_end_datetime" name="scheduled_end_datetime"
                                                   value="{{ old('scheduled_end_datetime', $fieldVisit->scheduled_end_datetime->format('Y-m-d\TH:i')) }}" required>
                                            @error('scheduled_end_datetime')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Note:</strong> Changes to this visit request will require re-approval from your reporting manager.
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 d-flex justify-content-center gap-3 mt-3">
                                       <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                       <i class="fas fa-save me-2"></i>Update Visit
                                       </button>
                                       <a href="{{ route('field-visits.show', $fieldVisit) }}" class="btn btn-danger px-4 rounded-pill shadow-sm">
                                       <i class="fas fa-times me-2"></i>Cancel
                                       </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set minimum date for datetime inputs
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDateTime = tomorrow.toISOString().slice(0, 16);

    $('#scheduled_start_datetime, #scheduled_end_datetime').attr('min', minDateTime);

    // Validate end time is after start time
    $('#scheduled_end_datetime').change(function() {
        const startTime = $('#scheduled_start_datetime').val();
        const endTime = $(this).val();

        if (startTime && endTime && endTime <= startTime) {
            alert('End time must be after start time');
            $(this).val('');
        }
    });
});
</script>
@endpush
