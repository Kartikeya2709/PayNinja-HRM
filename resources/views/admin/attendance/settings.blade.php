@extends('layouts.app')

@section('title', 'Attendance Settings')

@section('content')
<div class="container-fluid">
    <div class="row attendance-setting">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Attendance Settings
                        </h5>
                        <div class="attendance-btn">
                            <a href="{{ route('admin.attendance.settings.view') }}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-eye me-1"></i> View Current Settings
                            </a>
                            <button type="submit" form="attendance-settings-form" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.attendance.settings.update') }}" method="POST" id="attendance-settings-form" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-medium">Company</label>
                                    <input type="text" class="form-control bg-light" value="{{ $company->name }}" readonly>
                                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                                    <small class="text-muted">You can only manage settings for your company</small>
                                </div>
                            </div>
                        </div>

                        <!-- Office Hours Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Office Hours</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="office_start_time" class="form-label">Office Start Time</label>
                                            @php
    $officeStart = old('office_start_time', $settings->office_start_time ?? '09:00:00');
    try {
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $officeStart)) {
            $officeStart = \Carbon\Carbon::createFromFormat('H:i:s', $officeStart)->format('H:i');
        } elseif (preg_match('/^\d{2}:\d{2}$/', $officeStart)) {
            // already in correct format
        } else {
            $officeStart = '09:00';
        }
    } catch (\Exception $e) {
        $officeStart = '09:00';
    }
@endphp
                                            <input type="time" class="form-control" id="office_start_time" 
                                                   name="office_start_time" step="300"
                                                   value="{{ $officeStart }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="office_end_time" class="form-label">Office End Time</label>
                                            @php
    $officeEnd = old('office_end_time', $settings->office_end_time ?? '18:00:00');
    try {
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $officeEnd)) {
            $officeEnd = \Carbon\Carbon::createFromFormat('H:i:s', $officeEnd)->format('H:i');
        } elseif (preg_match('/^\d{2}:\d{2}$/', $officeEnd)) {
            // already in correct format
        } else {
            $officeEnd = '18:00';
        }
    } catch (\Exception $e) {
        $officeEnd = '18:00';
    }
@endphp
                                            <input type="time" class="form-control" id="office_end_time" 
                                                   name="office_end_time" step="300"
                                                   value="{{ $officeEnd }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="work_hours" class="form-label fw-medium">Minimum Work Hours (per day)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="work_hours" 
                                                       name="work_hours" min="1" max="24" step="0.5"
                                                       value="{{ old('work_hours', $settings ? $settings->work_hours : 8) }}" required>
                                                <span class="input-group-text">hours</span>
                                            </div>
                                            <small class="text-muted">Standard working hours per day(CheckOut before it will be marked as Half Day)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="grace_period" class="form-label fw-medium">Grace Period</label>
                                            @php
                                                $gracePeriod = old('grace_period', $settings->grace_period ?? '00:15');
                                                try {
                                                    if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $gracePeriod)) {
                                                        $gracePeriod = \Carbon\Carbon::createFromFormat('H:i:s', $gracePeriod)->format('H:i');
                                                    } elseif (preg_match('/^\d{2}:\d{2}$/', $gracePeriod)) {
                                                        // already in correct format
                                                    } else {
                                                        $gracePeriod = '00:15';
                                                    }
                                                } catch (\Exception $e) {
                                                    $gracePeriod = '00:15';
                                                }
                                            @endphp
                                            <input type="time" class="form-control" id="grace_period" 
                                                   name="grace_period" step="300"
                                                   value="{{ $gracePeriod }}" required>
                                            <small class="text-muted">Allowed late arrival time (e.g., 00:15)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="auto_absent_time" class="form-label">Auto Absent Time</label>
                                            @php
    $autoAbsentTime = old('auto_absent_time', $settings->auto_absent_time ?? '18:00');
    try {
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $autoAbsentTime)) {
            $autoAbsentTime = \Carbon\Carbon::createFromFormat('H:i:s', $autoAbsentTime)->format('H:i');
        } elseif (preg_match('/^\d{2}:\d{2}$/', $autoAbsentTime)) {
            // already in correct format
        } else {
            $autoAbsentTime = '18:00';
        }
    } catch (\Exception $e) {
        $autoAbsentTime = '18:00';
    }
@endphp
                                            <input type="time" class="form-control" id="auto_absent_time" 
                                                   name="auto_absent_time" step="300"
                                                   value="{{ $autoAbsentTime }}" required>
                                            <small class="form-text text-muted">Time after which employees will be marked as absent if not checked in (e.g., 11:00)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Office Hours Section -->

                        <!-- Attendance Settings Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user-clock me-2"></i>Attendance Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="allow_multiple_check_in" 
                                                   name="allow_multiple_check_in" value="1" 
                                                   {{ old('allow_multiple_check_in', $settings->allow_multiple_check_in ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium" for="allow_multiple_check_in">
                                                Allow Multiple Check-ins
                                            </label>
                                            <div class="ms-4 mt-1">
                                                <small class="text-muted">Employees can check in/out multiple times per day</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="track_location" 
                                                   name="track_location" value="1"
                                                   {{ isset($settings) && $settings->track_location ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium" for="track_location">
                                                Track Employee Location
                                            </label>
                                            <div class="ms-4 mt-1">
                                                <small class="text-muted">Record GPS coordinates during check-in/out</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Attendance Settings Section -->

                        <!-- Geolocation Settings Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Geolocation Settings</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_geolocation" 
                                           name="enable_geolocation" value="1"
                                           {{ isset($settings) && $settings->enable_geolocation ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium" for="enable_geolocation">
                                        Enable Location Restriction
                                    </label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Restrict attendance marking to specific location and radius
                                </div>
                                <div id="geolocation-fields" style="display: {{ isset($settings) && $settings->enable_geolocation ? 'block' : 'none' }};">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="office_latitude" class="form-label">Office Latitude</label>
                                                <input type="number" step="0.000001" class="form-control" id="office_latitude" 
                                                       name="office_latitude" value="{{ $settings->office_latitude ?? '' }}"
                                                       {{ isset($settings) && $settings->enable_geolocation ? 'required' : '' }}>
                                                <small class="text-muted">e.g., 28.6139</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="office_longitude" class="form-label">Office Longitude</label>
                                                <input type="number" step="0.000001" class="form-control" id="office_longitude" 
                                                       name="office_longitude" value="{{ $settings->office_longitude ?? '' }}"
                                                       {{ isset($settings) && $settings->enable_geolocation ? 'required' : '' }}>
                                                <small class="text-muted">e.g., 77.2090</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="geofence_radius" class="form-label">Allowed Radius (meters)</label>
                                                <input type="range" class="form-range" id="geofence_radius" 
                                                       name="geofence_radius" min="20" max="1000" step="10" 
                                                       value="{{ $settings->geofence_radius ?? 100 }}"
                                                       {{ isset($settings) && $settings->enable_geolocation ? 'required' : '' }}>
                                                <div class="d-flex justify-content-between">
                                                    <small>20m</small>
                                                    <span id="radius-value">{{ $settings->geofence_radius ?? 100 }}m</span>
                                                    <small>1000m</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="get-location">
                                                <i class="fas fa-map-marker-alt me-1"></i> Use Current Location
                                            </button>
                                        </div>
                                    </div>
                                   
                                    <!-- Map Container -->
                                    <div class="mb-3">
                                        <div id="map" style="height: 300px; width: 100%; border: 1px solid #dee2e6; border-radius: 4px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Geolocation Settings Section -->

                        <!-- Weekend Settings Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Weekend Configuration</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Select the weekend days for your organization:</p>
                                <div class="row">
                                    @php
                                        $weekendDays = [];
                                        if (isset($settings)) {
                                            $rawWeekendDays = $settings->weekend_days ?? '';
                                            if (is_string($rawWeekendDays)) {
                                                $decoded = json_decode($rawWeekendDays, true);
                                                $weekendDays = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
                                            } elseif (is_array($rawWeekendDays)) {
                                                $weekendDays = $rawWeekendDays;
                                            }
                                        }
                                        if (empty($weekendDays)) {
                                            $weekendDays = old('weekend_days', ['Saturday', 'Sunday']);
                                        }
                                        $weekendDays = is_array($weekendDays) ? $weekendDays : [];
                                    @endphp
                                    @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                        <div class="col-md-3 col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input weekend-day" 
                                                       type="checkbox" 
                                                       name="weekend_days[]" 
                                                       value="{{ $day }}" 
                                                       id="weekend_{{ strtolower($day) }}"
                                                       {{ in_array($day, $weekendDays) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="weekend_{{ strtolower($day) }}">
                                                    {{ $day }}
                                                </label>
                                            </div>
                                        </div>
                                        @if ($day === 'Saturday')
                                            <div class="col-md-9 col-12 mb-2 offset-md-3">
                                                <div class="form-check ms-3">
                                                    <input class="form-check-input weekend-alt-saturday" type="checkbox" name="weekend_days[]" value="saturday_1_3" id="weekend_saturday_1_3" {{ in_array('saturday_1_3', $weekendDays) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="weekend_saturday_1_3">
                                                        1st & 3rd Saturday
                                                    </label>
                                                </div>
                                                <div class="form-check ms-3">
                                                    <input class="form-check-input weekend-alt-saturday" type="checkbox" name="weekend_days[]" value="saturday_2_4" id="weekend_saturday_2_4" {{ in_array('saturday_2_4', $weekendDays) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="weekend_saturday_2_4">
                                                        2nd & 4th Saturday
                                                    </label>
                                                </div>
                                                <div class="form-check ms-3">
                                                    <input class="form-check-input weekend-alt-saturday" type="checkbox" name="weekend_days[]" value="saturday_1_3_5" id="weekend_saturday_1_3_5" {{ in_array('saturday_1_3_5', $weekendDays) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="weekend_saturday_1_3_5">
                                                        1st, 3rd & 5th Saturday
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <!-- End Weekend Settings Section -->

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save All Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #map {
        border-radius: 4px;
        min-height: 300px;
    }
    .map-container {
        position: relative;
    }
</style>
@endpush

@push('scripts')
<!-- jQuery Validation Plugin -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>

<script>
    let myMap, marker, circle;
    
    const getLocationBtn = document.getElementById('get-location');
    const latitudeInput = document.getElementById('office_latitude');
    const longitudeInput = document.getElementById('office_longitude');

    const olaMaps = new OlaMaps({
        apiKey: "{{ config('services.krutrim.maps_api_key') }}"
    });

    const defaultLocation = [
        parseFloat('{{ $settings->office_longitude ?? 77.22378292484578 }}'),
        parseFloat('{{ $settings->office_latitude ?? 28.63453194502196 }}')
    ];

    function setLocation(defaultLocation) {
        console.log('Setting location:', defaultLocation);
        
        myMap = olaMaps.init({
            style: "https://api.olamaps.io/tiles/vector/v1/styles/default-light-standard/style.json",
            container: 'map',
            center: defaultLocation,
            zoom: 15
        });

        // Add marker after map is loaded
        myMap.on('load', () => {
            marker = olaMaps
                .addMarker({ offset: [0, -15], anchor: 'bottom', color: 'red', draggable: true })
                .setLngLat(defaultLocation)
                .addTo(myMap);

            // Attach the drag event
            marker.on('drag', () => {
                const lngLat = marker.getLngLat();
                console.log('Marker dragged to:', lngLat);
                
                // Update the input fields with the new coordinates
                latitudeInput.value = lngLat.lat.toFixed(6);
                longitudeInput.value = lngLat.lng.toFixed(6);

                // Log the values being set
                console.log('Setting latitude:', latitudeInput.value);
                console.log('Setting longitude:', longitudeInput.value);
            });

            // Also attach dragend event for final position
            marker.on('dragend', () => {
                const lngLat = marker.getLngLat();
                console.log('Marker drag ended at:', lngLat);
                
                // Update the input fields with the final coordinates
                latitudeInput.value = lngLat.lat.toFixed(6);
                longitudeInput.value = lngLat.lng.toFixed(6);

                // Log the final values
                console.log('Final latitude:', latitudeInput.value);
                console.log('Final longitude:', longitudeInput.value);
            });
        });
    }
    
    // Initialize map with default location
    setLocation(defaultLocation);
    
    // Handle get location button click
    if (getLocationBtn) {
        getLocationBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }

            const button = this;
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Getting location...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Success callback
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    console.log('Got current location:', { lat, lng });
                    
                    // Update input fields
                    latitudeInput.value = lat.toFixed(6);
                    longitudeInput.value = lng.toFixed(6);
                    
                    // Update map
                    const newPosition = [lng, lat]; // Ola Maps uses [longitude, latitude]
                    if (marker && myMap) {
                        marker.setLngLat(newPosition);
                        myMap.setCenter(newPosition);
                    }
                    
                    button.disabled = false;
                    button.innerHTML = originalText;
                },
                function(error) {
                    // Error callback
                    console.error('Error getting location:', error);
                    alert('Error getting your location. Please make sure location services are enabled.');
                    button.disabled = false;
                    button.innerHTML = originalText;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }

    // Handle geofence radius change
    $('#geofence_radius').on('input', function() {
        $('#radius-value').text(this.value + 'm');
    });

    // Handle enable geolocation toggle
    $('#enable_geolocation').change(function() {
        const geolocationFields = $('#geolocation-fields');
        if (this.checked) {
            geolocationFields.slideDown();
            $('#office_latitude, #office_longitude, #geofence_radius').prop('required', true);
        } else {
            geolocationFields.slideUp();
            $('#office_latitude, #office_longitude, #geofence_radius').prop('required', false);
        }
    });

    // Form validation
    $('#attendance-settings-form').validate({
        rules: {
            company_id: 'required',
            office_start_time: 'required',
            office_end_time: {
                required: true,
                greaterThan: '#office_start_time'
            },
            work_hours: {
                required: true,
                min: 1,
                max: 24
            },
            late_minutes: {
                required: true,
                min: 0
            },
            early_leave_minutes: {
                required: true,
                min: 0
            },
            half_day_minutes: {
                required: true,
                min: 0
            },
            auto_absent_time: 'required',
            grace_period: 'required',
            geofence_radius: {
                required: '#enable_geolocation:checked',
                min: 50,
                max: 5000
            },
            office_latitude: {
                required: '#enable_geolocation:checked',
                number: true
            },
            office_longitude: {
                required: '#enable_geolocation:checked',
                number: true
            }
        },
        messages: {
            office_start_time: 'Please enter office start time',
            office_end_time: {
                required: 'Please enter office end time',
                greaterThan: 'End time must be after start time'
            },
            work_hours: {
                required: 'Please enter work hours',
                min: 'Work hours must be at least 1 hour',
                max: 'Work hours cannot exceed 24 hours'
            },
            late_minutes: {
                required: 'Please enter late minutes',
                min: 'Late minutes cannot be negative'
            },
            early_leave_minutes: {
                required: 'Please enter early leave minutes',
                min: 'Early leave minutes cannot be negative'
            },
            half_day_minutes: {
                required: 'Please enter half day minutes',
                min: 'Half day minutes cannot be negative'
            },
            auto_absent_time: 'Please enter auto absent time',
            grace_period: 'Please enter grace period',
            geofence_radius: {
                required: 'Please enter geofence radius',
                min: 'Minimum radius is 50 meters',
                max: 'Maximum radius is 5000 meters'
            },
            office_latitude: {
                required: 'Please set office location on map',
                number: 'Latitude must be a valid number'
            },
            office_longitude: {
                required: 'Please set office location on map',
                number: 'Longitude must be a valid number'
            },
            'weekend_days[]': {
                required: 'Please select at least one weekend day'
            }
        },
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function(form) {
            console.log('Form validation passed, submitting...');
            submitForm(form);
            return false; // Prevent normal form submission
        }
    });
</script>
<script>
    // Mutually exclusive selection for Saturday and alternate Saturdays
    document.addEventListener('DOMContentLoaded', function () {
        const regularSat = document.getElementById('weekend_saturday');
        const altSats = [
            document.getElementById('weekend_saturday_1_3'),
            document.getElementById('weekend_saturday_2_4'),
            document.getElementById('weekend_saturday_1_3_5')
        ];
        function toggleAltSaturdays() {
            if (regularSat && regularSat.checked) {
                altSats.forEach(cb => {
                    if(cb) { cb.checked = false; cb.disabled = true; }
                });
            } else {
                altSats.forEach(cb => { if(cb) cb.disabled = false; });
            }
        }
        function toggleRegularSaturday() {
            if (altSats.some(cb => cb && cb.checked)) {
                if(regularSat) { regularSat.checked = false; regularSat.disabled = true; }
            } else {
                if(regularSat) regularSat.disabled = false;
            }
        }
        if (regularSat) {
            regularSat.addEventListener('change', toggleAltSaturdays);
            altSats.forEach(cb => { if(cb) cb.addEventListener('change', toggleRegularSaturday); });
            // Initial state
            toggleAltSaturdays();
            toggleRegularSaturday();
        }
    });
</script>
@endpush
