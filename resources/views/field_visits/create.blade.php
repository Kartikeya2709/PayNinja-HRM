@extends('layouts.app')
@section('title', 'Schedule Field Visit')

@section('content')
<div class="main-content-01 container">
    <section class="section">
        <div class="section-header">
            <h1>Schedule Field Visit</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('field-visits.index') }}">Field Visits</a></div>
                <div class="breadcrumb-item active">Schedule New Visit</div>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header justify-content-center">
                            <h5 class="mb-3">Visit Details</h5>
                        </div>
                            <div class="card-body">
                            <form action="{{ route('field-visits.store') }}" method="POST" id="fieldVisitForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <div class="form-group">
                                            <label for="reporting_manager">Reporting Manager</label>
                                            <input type="text" class="form-control"
                                                   value="{{ auth()->user()->employee->reportingManager->name ?? 'No Reporting Manager' }}"
                                                   readonly>
                                            <small class="form-text text-muted">From your employee profile</small>
                                        </div>
                                    </div>
                                
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="visit_title">Visit Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('visit_title') is-invalid @enderror"
                                                   id="visit_title" name="visit_title" value="{{ old('visit_title') }}" required>
                                            @error('visit_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                               

                                    
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="visit_description">Visit Description</label>
                                            <textarea class="form-control @error('visit_description') is-invalid @enderror"
                                                      id="visit_description" name="visit_description" rows="3">{{ old('visit_description') }}</textarea>
                                            @error('visit_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                          

                                      
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="location_name">Location Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('location_name') is-invalid @enderror"
                                                   id="location_name" name="location_name" value="{{ old('location_name') }}" required>
                                            @error('location_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="location_address">Location Address <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('location_address') is-invalid @enderror"
                                                   id="location_address" name="location_address" value="{{ old('location_address') }}" required>
                                            @error('location_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                               

                                
                                    <div class="col-md-12 mb-4">
                                        <div class="form-group">
                                            <label for="visit_notes">Visit Notes</label>
                                            <textarea class="form-control @error('visit_notes') is-invalid @enderror"
                                                      id="visit_notes" name="visit_notes" rows="4">{{ old('visit_notes') }}</textarea>
                                            <small class="form-text text-muted">Please provide detailed notes about the purpose and objectives of this visit</small>
                                            @error('visit_notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <div class="form-group">
                                            <label for="visit_photos">Visit Photos</label>
                                            <input type="file" class="form-control @error('visit_photos.*') is-invalid @enderror"
                                                   id="visit_photos" name="visit_photos[]" multiple accept="image/*">
                                            <small class="form-text text-muted">Upload relevant photos for this visit (optional, max 20MB each)</small>
                                            @error('visit_photos.*')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-primary" id="getCurrentLocation">
                                                <i class="bi bi-geo-alt-fill me-2"></i> Update Location
                                            </button>
                                        </div>
                                        
                                        <div id="map" style="height: 300px; display:none;" class="rounded mt-3 position-relative"></div>
                                        
                                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                                        
                                        @error('latitude')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        @error('longitude')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="scheduled_start_datetime">Start Date</label>
                                            <input type="date" class="form-control @error('scheduled_start_datetime') is-invalid @enderror"
                                                   id="scheduled_start_datetime" name="scheduled_start_datetime"
                                                   value="{{ old('scheduled_start_datetime') }}">
                                            @error('scheduled_start_datetime')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="scheduled_end_datetime">End Date</label>
                                            <input type="date" class="form-control @error('scheduled_end_datetime') is-invalid @enderror"
                                                   id="scheduled_end_datetime" name="scheduled_end_datetime"
                                                   value="{{ old('scheduled_end_datetime') }}">
                                            @error('scheduled_end_datetime')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Note:</strong> This field visit request will be sent to your reporting manager for approval. Please ensure all details including location, notes, and photos are accurate as they cannot be changed after submission.
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 d-flex justify-content-center gap-3 mt-4">
                                       <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                       <i class="fas fa-paper-plane me-2"></i>Submit for Approval
                                       </button>
                                       <a href="{{ route('field-visits.index') }}" class="btn btn-danger px-4 rounded-pill shadow-sm">
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

@push('styles')
<link rel="stylesheet" href="https://api.olamaps.io/olamaps/1.0.0/olamaps.css">
@endpush

@push('scripts')
<script src="https://api.olamaps.io/olamaps/1.0.0/olamaps.min.js"></script>
<script>
$(document).ready(function() {
    // Set minimum date for date inputs
    const now = new Date();
    const minDate = now.toISOString().slice(0, 10);

    $('#scheduled_start_datetime, #scheduled_end_datetime').attr('min', minDate);

    let myMap = null;
    let userMarker = null;
    const OLA_API_KEY = "{{ config('services.krutrim.maps_api_key') }}";

    function resetLocationButton() {
        $('#getCurrentLocation').prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
        $('#map').hide();
        $('#latitude').val('');
        $('#longitude').val('');
        $('#map .map-loader').remove();
        if(userMarker && typeof userMarker.remove === 'function'){ userMarker.remove(); userMarker = null; }
        if(myMap && typeof myMap.resize === 'function') myMap.resize();
    }

    // Get current location
    $('#getCurrentLocation').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Getting location...');

        if (!navigator.geolocation) {
            alert('Geolocation is not supported by this browser.');
            resetLocationButton();
            return;
        }

        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude ?? null;
            const lng = position.coords.longitude ?? null;

            if (lat == null || lng == null) {
                alert('Unable to get valid coordinates.');
                resetLocationButton();
                return;
            }

            $('#latitude').val(lat);
            $('#longitude').val(lng);
            $('#map').show();

            // Map loader overlay
            $('#map').append('<div class="map-loader" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.7);display:flex;align-items:center;justify-content:center;z-index:1000;"><span class="spinner-border text-primary"></span></div>');

            try {
                const olaMaps = new OlaMaps({ apiKey: OLA_API_KEY });

                if (!myMap) {
                    myMap = olaMaps.init({
                        container: 'map',
                        center: [lng, lat],
                        zoom: 15,
                        style: "https://api.olamaps.io/tiles/vector/v1/styles/default-light-standard/style.json"
                    });
                }

                // User marker
                if (!userMarker) {
                    userMarker = olaMaps.addMarker({ color: 'red', draggable: false }).setLngLat([lng, lat]).addTo(myMap);
                } else {
                    userMarker.setLngLat([lng, lat]);
                }

                if (typeof myMap.setCenter === 'function') myMap.setCenter([lng, lat]);
                if (typeof myMap.resize === 'function') myMap.resize();

            } catch (e) {
                console.error('Map error:', e);
                alert('Error initializing map: ' + e.message);
            } finally {
                btn.prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
                $('#map .map-loader').remove();
            }
        }, function(err) {
            alert('Unable to fetch location: ' + err.message);
            resetLocationButton();
        }, { enableHighAccuracy: true, timeout: 10000 });
    });

    // Form validation handled by backend validation rules
    $('#fieldVisitForm').submit(function() {
        // Backend will handle date validation
        return true;
    });
});
</script>
@endpush
