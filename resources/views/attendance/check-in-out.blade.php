@extends('attendance.layout')

@section('title', 'Check In/Out')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card check-in-text">
            <div class="card-body text-center">
                <h3 class="mb-4">
                    @if(!$todayAttendance || !$todayAttendance->check_in)
                        Check In
                    @elseif(!$todayAttendance->check_out)
                        Check Out
                    @else
                        Attendance Recorded
                    @endif
                </h3>
                
                <!-- Current Time -->
                <div class="mb-4">
                    <div class="display-4 mb-2" id="currentTime">--:--:--</div>
                    <div class="text-muted" id="currentDate">-- --- ----</div>
                </div>
                
                <!-- Office Timings -->
                <div class="office-timings mb-4">
                    <div class="row justify-content-center check-in">
                        <div class="col-md-10">
                            <div class="card check-in-bg">
                                <div class="card-body p-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="text-muted small">Check In</div>
                                            <div class="h5 mb-0">
                                                @if(isset($todayAttendance) && $todayAttendance && $todayAttendance->check_in)
                                                    {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}
                                                @else
                                                    --
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-4 border-start border-end">
                                            <div class="text-muted small">Check Out </div>
                                            <div class="h5 mb-0">
                                                @if(isset($todayAttendance) && $todayAttendance && $todayAttendance->check_out)
                                                    {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}
                                                @else
                                                    --
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted small">Grace Period</div>
                                            <div class="h5 mb-0">
                                                @if(isset($settings) && $settings && $settings->grace_period)
                                                    {{ \Carbon\Carbon::parse($settings->grace_period)->format('h:i A') }}
                                                @else
                                                    --
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Geolocation Status (if enabled) -->
                @if(isset($settings) && $settings->enable_geolocation)
                <div id="locationStatus" class="alert alert-info mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span>Please update your location before checking in/out</span>
                </div>
                
                <!-- Location Button (if geolocation is enabled) -->
                <div class="d-grid gap-3 col-md-8 mx-auto mb-4">
                    <button id="getLocationBtn" class="btn button btn-lg">
                        <i class="bi bi-geo-alt-fill me-2"></i> Update Location
                    </button>
                </div>
                
                <!-- Map Container (initially hidden) -->
                <div id="map" style="height: 250px; width: 100%; margin-bottom: 20px; display: none;" class="rounded"></div>
                @endif
                
                <!-- Check In/Out Buttons -->
                <div class="d-grid gap-3 alert-info col-md-8 mx-auto">
                    @if($isWeekend)
                        <div class="alert alert-info mb-0">
                            <strong>Today is a weekend. Enjoy your day off!</strong>
                        </div>
                    @elseif(!$todayAttendance || !$todayAttendance->check_in)
                        <button id="checkInBtn" class="btn btn-primary btn-lg" {{ $settings->enable_geolocation ? 'disabled' : '' }}>
                            <i class="bi bi-box-arrow-in-right me-2"></i> Check In
                        </button>
                    @elseif(!$todayAttendance->check_out)
                        <button id="checkOutBtn" class="btn btn-danger btn-lg" {{ $settings->enable_geolocation ? 'disabled' : '' }}>
                            <i class="bi bi-box-arrow-right me-2"></i> Check Out
                        </button>
                    @else
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            You have completed your attendance for today.
                        </div>
                    @endif
                </div>
                
                <!-- Remarks Input (only shown when check-in/out is possible) -->
                <!-- @if(!$todayAttendance || !$todayAttendance->check_in || (!$todayAttendance->check_out && $todayAttendance->check_in))
                <div class="mt-4 col-md-8 mx-auto">
                    <div class="form-floating">
                        <textarea class="form-control" id="remarks" style="height: 80px" placeholder="Add remarks (optional)"></textarea>
                        <label for="remarks">Remarks (optional)</label>
                    </div>
                </div>
                @endif -->
                
                <!-- Today's Status -->
                @if($todayAttendance)
                    <div class="mt-4 pt-3 border-top">
                        <h5>Today's Attendance Status</h5>
                        <div class="d-flex justify-content-center">
                            <div class="text-start w-100">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        @if($todayAttendance->check_in)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <i class="bi bi-arrow-right-circle-fill text-success me-2"></i>
                                                    <strong>Check In:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}
                                                </div>
                                                @if($todayAttendance->check_in_status)
                                                    <span class="badge bg-{{ $todayAttendance->check_in_status === 'On Time' ? 'success' : 'warning' }}">
                                                        {{ $todayAttendance->check_in_status }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($todayAttendance->check_in_remarks)
                                                <div class="mb-2 ps-4 small text-muted">
                                                    <i class="bi bi-chat-left-text-fill me-1"></i> {{ $todayAttendance->check_in_remarks }}
                                                </div>
                                            @endif
                                        @endif
                                        
                                        @if($todayAttendance->check_out)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <i class="bi bi-arrow-left-circle-fill text-danger me-2"></i>
                                                    <strong>Check Out:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}
                                                </div>
                                                @if($todayAttendance->check_out_status)
                                                    <span class="badge bg-{{ $todayAttendance->check_out_status === 'On Time' ? 'success' : 'warning' }}">
                                                        {{ $todayAttendance->check_out_status }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($todayAttendance->check_out_remarks)
                                                <div class="mb-2 ps-4 small text-muted">
                                                    <i class="bi bi-chat-left-text-fill me-1"></i> {{ $todayAttendance->check_out_remarks }}
                                                </div>
                                            @endif
                                        @endif
                                        

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #map { border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Global variables
    let myMap = null;
    let marker = null;
    let currentPosition = null;
    let isLocationValid = false;
    let geolocationRequired = {{ $settings->enable_geolocation ? 'true' : 'false' }};
    
    // Initialize Ola Maps
    const olaMaps = new OlaMaps({
        apiKey: "{{ config('services.krutrim.maps_api_key') }}"
    });
    
    // Update current time
    function updateCurrentTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit', 
            hour12: true 
        });
        const dateStr = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        $('#currentTime').text(timeStr);
        $('#currentDate').text(dateStr);
    }

    // Update time every second
    setInterval(updateCurrentTime, 1000);
    updateCurrentTime();
    
    // Show alert function
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove any existing alerts
        $('.alert-dismissible').alert('close');
        
        // Add and show the new alert
        $('.card-body').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert-dismissible').alert('close');
        }, 5000);
    }
    
    // Initialize Map
    function initMap(defaultLocation) {
        myMap = olaMaps.init({
            style: "https://api.olamaps.io/tiles/vector/v1/styles/default-light-standard/style.json",
            container: 'map',
            center: defaultLocation,
            zoom: 15
        });

        // Add marker after map is loaded
        myMap.on('load', () => {
            marker = olaMaps
                .addMarker({ offset: [0, -15], anchor: 'bottom', color: 'red', draggable: false })
                .setLngLat(defaultLocation)
                .addTo(myMap);

            // Add office marker if office location is set
            @if($settings->office_latitude && $settings->office_longitude)
            const officeLocation = [
                parseFloat('{{ $settings->office_longitude }}'),
                parseFloat('{{ $settings->office_latitude }}')
            ];
            
            // Add office marker
            const officeMarker = olaMaps
                .addMarker({ offset: [0, -15], anchor: 'bottom', color: 'blue' })
                .setLngLat(officeLocation)
                .addTo(myMap);
            
            // Add geofence circle
            const geofenceRadius = {{ $settings->geofence_radius ?? 100 }};
            const circle = olaMaps.addCircle({
                center: officeLocation,
                radius: geofenceRadius,
                fillColor: '#4285F4',
                fillOpacity: 0.1,
                strokeColor: '#4285F4',
                strokeOpacity: 0.8,
                strokeWidth: 2
            }).addTo(myMap);
            @endif
        });
    }
    
    // Get current location
    function getCurrentLocation() {
        const $btn = $('#getLocationBtn');
        const $status = $('#locationStatus');
        
        // Update UI
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Getting Location...');
        $status.removeClass('alert-success alert-danger').addClass('alert-info').html('<i class="bi bi-info-circle-fill me-2"></i><span>Getting your location...</span>');
        
        // Check if geolocation is supported
        if (!navigator.geolocation) {
            $status.removeClass('alert-info').addClass('alert-danger').html('<i class="bi bi-exclamation-triangle-fill me-2"></i><span>Geolocation is not supported by your browser</span>');
            $btn.prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
            return;
        }
        
        // Get current position
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Success callback
                const { latitude, longitude, accuracy } = position.coords;
                currentPosition = [longitude, latitude]; // Note: Ola Maps uses [longitude, latitude] format
                
                // Update map
                if (myMap && marker) {
                    marker.setLngLat(currentPosition);
                    myMap.setCenter(currentPosition);
                    $('#map').show();
                } else {
                    initMap(currentPosition);
                    $('#map').show();
                }
                
                // Validate location
                validateLocation(latitude, longitude);
            },
            function(error) {
                // Error callback
                console.error('Error getting location:', error);
                $status.removeClass('alert-info').addClass('alert-danger').html(`
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span>Error getting your location: ${error.message}</span>
                `);
                $btn.prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
    
    // Validate location
    function validateLocation(latitude, longitude) {
        const $btn = $('#getLocationBtn');
        const $status = $('#locationStatus');
        
        // If geolocation is not required, always valid
        if (!geolocationRequired) {
            isLocationValid = true;
            $('#checkInBtn, #checkOutBtn').prop('disabled', false);
            return;
        }
        
        // Check if office coordinates are set
        @if($settings->office_latitude && $settings->office_longitude)
        const officePosition = {
            lat: {{ $settings->office_latitude }},
            lng: {{ $settings->office_longitude }}
        };
        
        // Calculate distance from office
        const distance = calculateDistance(
            latitude, 
            longitude, 
            officePosition.lat, 
            officePosition.lng
        );
        
        // Check if within geofence radius
        const geofenceRadius = {{ $settings->geofence_radius ?? 100 }};
        isLocationValid = distance <= geofenceRadius;
        
        if (isLocationValid) {
            // Within geofence
            $status.removeClass('alert-info alert-danger').addClass('alert-success').html(`
                <i class="bi bi-check-circle-fill me-2"></i>
                <span>Location verified! You are within ${Math.round(distance)}m of the office (max: ${geofenceRadius}m)</span>
            `);
            
            // Enable check-in/out buttons
            $('#checkInBtn, #checkOutBtn').prop('disabled', false);
        } else {
            // Outside geofence
            $status.removeClass('alert-info alert-success').addClass('alert-danger').html(`
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span>You are ${Math.round(distance)}m away from the office (max allowed: ${geofenceRadius}m)</span>
            `);
            
            // Disable check-in/out buttons
            $('#checkInBtn, #checkOutBtn').prop('disabled', true);
        }
        @else
        // No office coordinates set, assume valid
        isLocationValid = true;
        $status.removeClass('alert-info alert-danger').addClass('alert-success').html(`
            <i class="bi bi-check-circle-fill me-2"></i>
            <span>Location updated successfully!</span>
        `);
        
        // Enable check-in/out buttons
        $('#checkInBtn, #checkOutBtn').prop('disabled', false);
        @endif
        
        $btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise me-2"></i> Update Location');
    }
    
    // Calculate distance between two coordinates using Haversine formula
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth radius in meters
        const φ1 = lat1 * Math.PI/180; // φ, λ in radians
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c; // Distance in meters
    }
    
    // Handle check-in button click
    $('#checkInBtn').click(function() {
        performAction('check-in');
    });
    
    // Handle check-out button click
    $('#checkOutBtn').click(function() {
        performAction('check-out');
    });
    
    // Perform check-in or check-out action
    function performAction(action) {
        const $btn = action === 'check-in' ? $('#checkInBtn') : $('#checkOutBtn');
        const originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
        
        // Prepare data
        const data = {
            _token: '{{ csrf_token() }}',
            remarks: $('#remarks').val()
        };
        
        // Add location data if available
        if (currentPosition) {
            // Fix: Send coordinates in correct order [lat, lng]
            data.location = `${currentPosition[1]},${currentPosition[0]}`; // Convert from [lng,lat] to "lat,lng"
            data.latitude = currentPosition[1];  // Latitude
            data.longitude = currentPosition[0]; // Longitude
            
            console.log('Sending location data:', {
                location: data.location,
                latitude: data.latitude,
                longitude: data.longitude
            });
        }
        
        // Determine URL based on action
        const url = action === 'check-in' 
            ? '{{ route("attendance.check-in.post") }}' 
            : '{{ route("attendance.check-out.post") }}';
        
        // Send request
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    const message = action === 'check-in' 
                        ? 'Checked in successfully!' 
                        : 'Checked out successfully!';
                    
                    showAlert('success', message);
                    
                    // Reload page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message || 'An error occurred. Please try again.');
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                console.log('AJAX error:', xhr);
                
                let errorMessage = 'An error occurred. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                    console.log('Server error message:', errorMessage);
                } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Handle validation errors
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                showAlert('danger', errorMessage);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    }
    
    // Initialize page
    if (geolocationRequired) {
        // If geolocation is required, attach click handler to location button
        $('#getLocationBtn').on('click', function() {
            getCurrentLocation();
        });
    
        // Initialize map with default office location if available
        @if($settings->office_latitude && $settings->office_longitude)
        const defaultLocation = [
            parseFloat('{{ $settings->office_longitude }}'),
            parseFloat('{{ $settings->office_latitude }}')
        ];
        initMap(defaultLocation);
        @endif
    } else {
        // If geolocation is not required, enable buttons and hide location elements
        $('#checkInBtn, #checkOutBtn').prop('disabled', false);
        $('#locationStatus, #getLocationBtn').hide();
    }
});
</script>
@endpush