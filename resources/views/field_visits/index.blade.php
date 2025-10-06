@extends('layouts.app')
@section('title', 'Field Visits')

@section('content')
<section class="section container">
<section class="section container">
    <div class="section-header">
        <h1>Field Visits</h1>
        <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item active"><a href="">Field Visits</a></div>
    </div>

    <div class="section-body">
      @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
       @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Field Visits List</h5>
                        <div class="card-header-action">
                            <a href="{{ route('field-visits.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Schedule New Visit
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mt-2">
                            <div class="col-md-3 mb-4">
                                <select class="form-control" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-4">
                                <select class="form-control" id="approvalFilter">
                                    <option value="">All Approvals</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-4">
                                <input type="date" class="form-control" id="startDateFilter" placeholder="Start Date">
                            </div>
                            <div class="col-md-3 mb-4">
                                <input type="date" class="form-control" id="endDateFilter" placeholder="End Date">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped" id="fieldVisitsTable">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>Scheduled Date</th>
                                        <th>Status</th>
                                        <th>Approval</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fieldVisits as $visit)
                                        <tr>
                                            <td>{{ $visit->visit_title }}</td>
                                            <td>{{ $visit->location_name }}</td>
                                            <td>
                                                {{ $visit->scheduled_start_datetime 
                                                    ? \Carbon\Carbon::parse($visit->scheduled_start_datetime)->format('M d, Y H:i') 
                                                    : '-' 
                                                }}
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($visit->status==='scheduled') badge-info
                                                    @elseif($visit->status==='in_progress') badge-warning
                                                    @elseif($visit->status==='completed') badge-success
                                                    @else badge-secondary @endif">
                                                    {{ ucfirst(str_replace('_',' ',$visit->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($visit->approval_status==='pending') badge-warning
                                                    @elseif($visit->approval_status==='approved') badge-success
                                                    @else badge-danger @endif">
                                                    {{ ucfirst($visit->approval_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('field-visits.show', $visit) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @php
                                                    $canComplete = $visit->isInProgress() && $visit->employee_id === auth()->user()->employee->id;
                                                @endphp
                                                @if($canComplete)
                                                    <button class="btn btn-sm btn-success complete-visit-btn" data-id="{{ $visit->id }}">
                                                        <i class="fas fa-stop"></i> Complete
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center">No field visits found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">{{ $fieldVisits->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

@push('scripts')
<script src="https://api.olamaps.io/olamaps/1.0.0/olamaps.min.js"></script>
<script>
$(function () {
    let currentVisitId = null;
    let myMap = null;
    let userMarker = null;
    const OLA_API_KEY = "{{ config('services.krutrim.maps_api_key') }}";
    const officeCoords = [77.223786, 28.630901];

    function resetLocationButton() {
        $('#getLocationBtn').prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
        $('#map').hide();
        $('#latitude').val('');
        $('#longitude').val('');
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

    $('#getLocationBtn').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Getting location...');

        if(!navigator.geolocation) {
            alert('Geolocation not supported');
            btn.prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
            return;
        }

        navigator.geolocation.getCurrentPosition(function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            $('#latitude').val(lat);
            $('#longitude').val(lng);
            $('#map').show();

            try {
                if(!myMap){
                    const olaMaps = new OlaMaps({ apiKey: OLA_API_KEY });
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
                const olaMaps = new OlaMaps({ apiKey: OLA_API_KEY });
                if(!userMarker){
                    userMarker = olaMaps.addMarker({color:'red', draggable:false}).setLngLat([lng,lat]).addTo(myMap);
                }else{
                    userMarker.setLngLat([lng,lat]);
                }
                if(typeof myMap.setCenter==='function') myMap.setCenter([lng,lat]);
                if(typeof myMap.resize==='function') myMap.resize();
            } catch(e){
                console.error('Map error:', e);
                alert('Error initializing map: ' + e.message);
            } finally {
                btn.prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
            }
        }, function(err){
            alert('Unable to fetch location: ' + err.message);
            btn.prop('disabled', false).html('<i class="bi bi-geo-alt-fill me-2"></i> Update Location');
        }, {enableHighAccuracy:true, timeout:10000});
    });

    // === Table Filter JS ===
    $('#statusFilter, #approvalFilter, #startDateFilter, #endDateFilter').on('change', function() {
        const status = $('#statusFilter').val().toLowerCase();
        const approval = $('#approvalFilter').val().toLowerCase();
        const startDate = $('#startDateFilter').val();
        const endDate = $('#endDateFilter').val();

        $('#fieldVisitsTable tbody tr').each(function() {
            const row = $(this);
            const rowStatus = row.find('td:eq(3)').text().toLowerCase();
            const rowApproval = row.find('td:eq(4)').text().toLowerCase();
            const rowDateText = row.find('td:eq(2)').text().trim();
            let show = true;

            // Status filter
            if(status && rowStatus !== status) show = false;

            // Approval filter
            if(approval && rowApproval !== approval) show = false;

            // Date filter
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
    });
});
</script>
@endpush