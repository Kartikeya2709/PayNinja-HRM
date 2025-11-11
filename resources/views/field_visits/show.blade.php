@extends('layouts.app')
@section('title', 'Field Visit Details')

@section('content')
<div class="main-content-01 container">
    <section class="section">
        <div class="section-header">
            <h1>Field Visit Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('field-visits.index') }}">Field Visits</a></div>
                <div class="breadcrumb-item active">{{ $fieldVisit->visit_title }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ $fieldVisit->visit_title }}</h4>
                            <div class="card-header-action">
                                @php
                                    $user = auth()->user();
                                    $canEdit = ($fieldVisit->employee_id === $user->employee->id && $fieldVisit->isPendingApproval()) || $user->hasRole(['admin', 'company_admin']);
                                    $canApprove = ($user->hasRole(['admin', 'company_admin']) || $fieldVisit->reporting_manager_id === $user->employee->id) && $fieldVisit->isPendingApproval();
                                @endphp

                                @if($canEdit && $fieldVisit->isScheduled() && $fieldVisit->isPendingApproval())
                                    <a href="{{ route('field-visits.edit', $fieldVisit) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endif

                                @if($canApprove)
                                    <button class="btn btn-success btn-sm approve-btn" data-id="{{ $fieldVisit->id }}">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $fieldVisit->id }}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                @endif

                                @if($fieldVisit->isScheduled() && $fieldVisit->isApproved() && $fieldVisit->employee_id === auth()->user()->employee->id)
                                    <button class="btn btn-primary btn-sm start-visit-btn" data-id="{{ $fieldVisit->id }}">
                                        <i class="fas fa-play"></i> Start Visit
                                    </button>
                                @endif

                                @if($fieldVisit->isInProgress() && $fieldVisit->employee_id === auth()->user()->employee->id)
                                    <button class="btn btn-success btn-sm complete-visit-btn"
                                        data-id="{{ $fieldVisit->id }}">
                                        <i class="fas fa-stop"></i> Complete Visit
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <!-- Status Badges -->
                                <div class="col-12 mb-3">
                                    <div class="d-flex gap-2">
                                        @if($fieldVisit->status === 'scheduled')
                                            <span class="badge badge-info badge-lg">Scheduled</span>
                                        @elseif($fieldVisit->status === 'in_progress')
                                            <span class="badge badge-warning badge-lg">In Progress</span>
                                        @elseif($fieldVisit->status === 'completed')
                                            <span class="badge badge-success badge-lg">Completed</span>
                                        @else
                                            <span class="badge badge-secondary badge-lg">Cancelled</span>
                                        @endif

                                        @if($fieldVisit->approval_status === 'pending')
                                            <span class="badge badge-warning badge-lg">Pending Approval</span>
                                        @elseif($fieldVisit->approval_status === 'approved')
                                            <span class="badge badge-success badge-lg">Approved</span>
                                        @else
                                            <span class="badge badge-danger badge-lg">Rejected</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <div class="card visit-info">
                                        <div class="card-header">
                                            <h5>Visit Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <strong>Employee:</strong><br>
                                                    {{ $fieldVisit->employee->name }}
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <strong>Reporting Manager:</strong><br>
                                                    {{ $fieldVisit->reportingManager->name }}
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <strong>Description:</strong><br>
                                                    {{ $fieldVisit->visit_description ?: 'No description provided' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location Information -->
                                <div class="col-md-6">
                                    <div class="card visit-info">
                                        <div class="card-header">
                                            <h5>Location Details</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <strong>Location Name:</strong><br>
                                                    {{ $fieldVisit->location_name }}
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <strong>Address:</strong><br>
                                                    {{ $fieldVisit->location_address }}
                                                </div>
                                                @if($fieldVisit->latitude && $fieldVisit->longitude)
                                                    <div class="col-12 mb-3">
                                                        <strong>Coordinates:</strong><br>
                                                        {{ $fieldVisit->latitude }}, {{ $fieldVisit->longitude }}
                                                        <br>
                                                        <a href="https://www.google.com/maps?q={{ $fieldVisit->latitude }},{{ $fieldVisit->longitude }}"
                                                            target="_blank" class="btn btn-sm btn-info mt-1">
                                                            <i class="fas fa-map-marker-alt"></i> View on Map
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <!-- Schedule Information -->
                                <div class="col-md-6">
                                    <div class="card visit-info">
                                        <div class="card-header">
                                            <h5>Schedule</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <strong>Scheduled Start:</strong><br>
                                                    {{ $fieldVisit->scheduled_start_datetime->format('M d, Y H:i') }}
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <strong>Scheduled End:</strong><br>
                                                    {{ $fieldVisit->scheduled_end_datetime->format('M d, Y H:i') }}
                                                </div>
                                                @if($fieldVisit->actual_start_datetime)
                                                    <div class="col-12 mb-3">
                                                        <strong>Actual Start:</strong><br>
                                                        {{ $fieldVisit->actual_start_datetime->format('M d, Y H:i') }}
                                                    </div>
                                                @endif
                                                @if($fieldVisit->actual_end_datetime)
                                                    <div class="col-12 mb-3">
                                                        <strong>Actual End:</strong><br>
                                                        {{ $fieldVisit->actual_end_datetime->format('M d, Y H:i') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visit Notes & attachments -->
                                <div class="col-md-6">
                                    <div class="card visit-info">
                                        <div class="card-header">
                                            <h5>Visit Details</h5>
                                        </div>
                                        <div class="card-body">
                                            @if($fieldVisit->visit_notes)
                                                <div class="mb-3">
                                                    <strong>Visit Notes:</strong><br>
                                                    {{ $fieldVisit->visit_notes }}
                                                </div>
                                            @endif

                                            @if($fieldVisit->manager_feedback)
                                                <div class="mb-3">
                                                    <strong>Manager Feedback:</strong><br>
                                                    <div class="alert alert-info">
                                                        {{ $fieldVisit->manager_feedback }}
                                                    </div>
                                                </div>
                                            @endif

                                            @if($fieldVisit->visit_attachments && count($fieldVisit->visit_attachments) > 0)
                                                <div class="mb-3">
                                                    <strong>Visit Photos:</strong><br>
                                                    <div class="row">
                                                        @foreach($fieldVisit->visit_attachments as $attachment)
                                                            <a href="{{ Storage::url($attachment) }}" target="_blank"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="fas fa-image"></i> {{ basename($attachment) }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                                @if($fieldVisit->approved_at && $fieldVisit->approver)
                                                    <div class="mb-3">
                                                        <strong>Approved By:</strong><br>
                                                        {{ $fieldVisit->approver->name }} on
                                                        {{ $fieldVisit->approved_at->format('M d, Y H:i') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Field Visit</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approvalForm" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="manager_feedback">Feedback (Optional)</label>
                            <textarea class="form-control" id="manager_feedback" name="manager_feedback"
                                rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Field Visit</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" id="rejectionForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejection_feedback">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_feedback" name="manager_feedback" rows="3"
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Completion Modal -->
    <div class="modal fade" id="completionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="completionForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Complete Field Visit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="visit_notes">Visit Notes</label>
                        <textarea class="form-control" id="visit_notes" name="visit_notes" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="visit_attachments">Visit Photos</label>
                        <input type="file" class="form-control" id="visit_attachments" name="visit_attachments[]" multiple accept="image/*">
                        <small class="form-text text-muted">Multiple photos allowed (max 20MB each)</small>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="button" class="btn btn-primary" id="getLocationBtn">
                            <i class="bi bi-geo-alt-fill me-2"></i> Update Location
                        </button>
                    </div>

                    <div id="map" style="height: 300px; display:none;" class="rounded mb-3"></div>

                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="https://api.olamaps.io/olamaps/1.0.0/olamaps.css">
@endpush
<script src="https://api.olamaps.io/olamaps/1.0.0/olamaps.min.js"></script>
@push('scripts')
<script src="https://api.olamaps.io/olamaps/1.0.0/olamaps.min.js"></script>
<script>
$(document).ready(function () {
    let currentVisitId = {{ $fieldVisit->id }};
    let myMap = null;
    let userMarker = null;
    const OLA_API_KEY = "{{ config('services.krutrim.maps_api_key') }}";
    const officeCoords = [77.223786, 28.630901];

    // Approve/Reject/Start buttons
    $('.approve-btn').click(function () {
        $('#approvalForm').attr('action', '{{ url("field-visits") }}/' + currentVisitId + '/approve');
        $('#approvalModal').modal('show');
    });

    $('.reject-btn').click(function () {
        $('#rejectionForm').attr('action', '{{ url("field-visits") }}/' + currentVisitId + '/reject');
        $('#rejectionModal').modal('show');
    });

    $('.start-visit-btn').click(function () {
        if (confirm('Are you sure you want to start this field visit?')) {
            $.post('{{ url("field-visits") }}/' + currentVisitId + '/start', {_token: '{{ csrf_token() }}'})
            .done(function(){ location.reload(); })
            .fail(function(xhr){ alert('Error: '+xhr.responseJSON.message); });
        }
    });

    $('.complete-visit-btn').click(function () { $('#completionModal').modal('show'); });

            // Handle form submission
            $('#completionForm').on('submit', function (e) {
                var currentVisitId = {{ $fieldVisit->id }};
                $(this).attr('action', '{{ url("field-visits") }}/' + currentVisitId + '/complete');
            });
 
            //    let currentVisitId = null;
            let myMap = null;
            let userMarker = null;
            const OLA_API_KEY = "{{ config('services.krutrim.maps_api_key') }}";
            const officeCoords = [77.223786, 28.630901];

            function resetLocationButton() {
                $('#getLocationBtn').prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
                $('#map').hide();
                $('#latitude').val('');
                $('#longitude').val('');
                $('#map .map-loader').remove();
                if(userMarker && typeof userMarker.remove === 'function'){ userMarker.remove(); userMarker = null; }
                if(myMap && typeof myMap.resize === 'function') myMap.resize();
            }

            $('.complete-visit-btn').click(function() {
                currentVisitId = $(this).data('id');
                $('#completionForm').attr('action', '{{ url("field-visits") }}/' + currentVisitId + '/complete');
                $('#completionForm')[0].reset();
                resetLocationButton();
                $('#completionModal').modal('show');
            });

            $('#completionModal').on('hidden.bs.modal', resetLocationButton);
            $('#completionModal').on('shown.bs.modal', function() { if(myMap) myMap.resize(); });

            // === Get Location with Loader ===
            $('#getLocationBtn').click(function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Getting location...');

                if(!navigator.geolocation) {
                    alert('Geolocation not supported');
                    resetLocationButton();
                    return;
                }

                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lat = pos.coords.latitude ?? null;
                    const lng = pos.coords.longitude ?? null;

                    if(lat == null || lng == null){
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

                        if(!myMap){
                            myMap = olaMaps.init({
                                container:'map',
                                center:[lng, lat],
                                zoom:15,
                                style:"https://api.olamaps.io/tiles/vector/v1/styles/default-light-standard/style.json"
                            });

                            // Office marker
                            olaMaps.addMarker({color:'blue'}).setLngLat(officeCoords).addTo(myMap);

                            // Office circle
                            if(typeof olaMaps.addCircle==='function'){
                                olaMaps.addCircle({center:officeCoords,radius:50,fillColor:'#4285F4',fillOpacity:0.1,strokeColor:'#4285F4',strokeOpacity:0.8,strokeWidth:2}).addTo(myMap);
                            }
                        }

                        // User marker
                        if(!userMarker){
                            userMarker = olaMaps.addMarker({color:'red', draggable:false}).setLngLat([lng,lat]).addTo(myMap);
                        } else {
                            userMarker.setLngLat([lng,lat]);
                        }

                        if(typeof myMap.setCenter==='function') myMap.setCenter([lng,lat]);
                        if(typeof myMap.resize==='function') myMap.resize();

                    } catch(e){
                        console.error('Map error:', e);
                        alert('Error initializing map: ' + e.message);
                    } finally {
                        btn.prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
                        $('#map .map-loader').remove();
                    }
                }, function(err){
                    alert('Unable to fetch location: ' + err.message);
                    resetLocationButton();
                }, {enableHighAccuracy:true, timeout:10000});
            });

            // === Table Filter Loader ===
            $('#statusFilter, #approvalFilter, #startDateFilter, #endDateFilter').on('change', function() {
                const status = $('#statusFilter').val().toLowerCase();
                const approval = $('#approvalFilter').val().toLowerCase();
                const startDate = $('#startDateFilter').val();
                const endDate = $('#endDateFilter').val();

                $('#tableLoader').show();

                setTimeout(() => {
                    $('#fieldVisitsTable tbody tr').each(function() {
                        const row = $(this);
                        const rowStatus = row.find('td:eq(3)').text().toLowerCase();
                        const rowApproval = row.find('td:eq(4)').text().toLowerCase();
                        const rowDateText = row.find('td:eq(2)').text().trim();
                        let show = true;

                        if(status && rowStatus !== status) show = false;
                        if(approval && rowApproval !== approval) show = false;
                        if(startDate){
                            const rowDateObj = new Date(rowDateText);
                            if(rowDateObj < new Date(startDate)) show = false;
                        }
                        if(endDate){
                            const rowDateObj = new Date(rowDateText);
                            if(rowDateObj > new Date(endDate)) show = false;
                        }

                        row.toggle(show);
                    });

                    $('#tableLoader').hide();
                }, 200); // optional delay to show spinner effect
            });

            // Auto dismiss alerts after 3 sec
            setTimeout(() => {
                $('#successAlert, #errorAlert, #validationAlert').alert('close');
            }, 3000);
        });
    </script>
@endpush
