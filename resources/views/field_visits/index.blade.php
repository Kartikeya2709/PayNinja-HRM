@extends('layouts.app')
@section('title', 'Field Visits')

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>Field Visits</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Field Visits</div>
        </div>
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
                        {{-- Show pending approvals prominently for managers --}}
                        @php
                            $user = auth()->user();
                            $hasPending = false;
                            if ($user->hasRole(['admin', 'company_admin'])) {
                                $pendingCount = \App\Models\FieldVisit::where('approval_status', 'pending')->count();
                                $hasPending = $pendingCount > 0;
                            } elseif ($user->employee) {
                                $pendingCount = \App\Models\FieldVisit::where('reporting_manager_id', $user->employee->id)->where('approval_status', 'pending')->count();
                                $hasPending = $pendingCount > 0;
                            }
                        @endphp
                        @if($hasPending)
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle"></i> Pending Approvals</h5>
                                <p>You have field visit requests waiting for your approval.
                                    <a class="text-decoration-underline" href="{{ route('field-visits.pending') }}">View
                                        Pending Approvals</a>
                                </p>
                            </div>
                        @endif

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

                        <div class="table-responsive position-relative">
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
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('field-visits.show', $visit) }}"
                                                        class="btn btn-outline-info action-btn" data-id="{{ $visit->id }}"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="View Field Visit" aria-label="View">
                                                        <span class="btn-content">
                                                            <i class="fas fa-eye"></i>
                                                        </span>
                                                        <span class="spinner-border spinner-border-sm d-none" role="status"
                                                            aria-hidden="true"></span>
                                                    </a>


                                                    @php
                                                        $user = auth()->user();
                                                        $canEdit = ($visit->employee_id === $user->employee->id && $visit->isPendingApproval()) || $user->hasRole(['admin', 'company_admin']);
                                                        $canApprove = ($user->hasRole(['admin', 'company_admin']) || $visit->reporting_manager_id === $user->employee->id) && $visit->isPendingApproval();
                                                        $canStart = $visit->isScheduled() && $visit->isApproved() && $visit->employee_id === $user->employee->id;
                                                        $canComplete = $visit->isInProgress() && $visit->employee_id === $user->employee->id;
                                                    @endphp

                                                    @if($canEdit)
                                                        <a href="{{ route('field-visits.edit', $visit) }}"
                                                            class="btn btn-outline-warning action-btn"
                                                            data-id="{{ $visit->id }}" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" title="Edit Field Visit" aria-label="Edit">
                                                            <span class="btn-content">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                                aria-hidden="true"></span>
                                                        </a>
                                                    @endif

                                                    @if($canApprove)
                                                        <form action="{{ route('field-visits.approve', $visit) }}" method="POST"
                                                            style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success btn-sm action-btn rounded-0"
                                                                data-id="{{ $request->id ?? '' }}" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Approve Request"
                                                                aria-label="Approve">
                                                                <span class="btn-content">
                                                                    <i class="fa-solid fa-check"></i>
                                                                </span>
                                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                                    aria-hidden="true"></span>
                                                            </button>

                                                        </form>
                                                        <form action="{{ route('field-visits.reject', $visit) }}" method="POST"
                                                            style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger btn-sm action-btn rounded-start-0"
                                                                data-id="{{ $request->id ?? '' }}" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Reject Request" aria-label="Reject">
                                                                <span class="btn-content">
                                                                    <i class="fas fa-times"></i>
                                                                </span>
                                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                                    aria-hidden="true"></span>
                                                            </button>

                                                        </form>
                                                    @endif

                                                    @if($canStart)
                                                        <button type="button" class="btn btn-outline-primary btn-sm action-btn start-visit-btn"
                                                            data-id="{{ $visit->id }}" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" title="Start Visit" aria-label="Start">
                                                            <span class="btn-content">
                                                                <i class="fas fa-play"></i>
                                                                Start
                                                            </span>
                                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                                aria-hidden="true"></span>
                                                        </button>

                                                    @endif

                                                    @if($canComplete)
                                                        <button type="button"
                                                            class="btn btn-outline-success btn-sm action-btn complete-visit-btn"
                                                            data-id="{{ $visit->id }}" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" title="Complete Visit" aria-label="Complete">
                                                            <span class="btn-content">
                                                                <i class="fas fa-stop"></i>
                                                                Complete
                                                            </span>
                                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                                aria-hidden="true"></span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center">No field visits found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <!-- Table Loader -->
                            <div id="tableLoader" style="
                                position:absolute; top:0; left:0; width:100%; height:100%;
                                background: rgba(255,255,255,0.7);
                                display:flex; align-items:center; justify-content:center;
                                z-index:1000; display:none;">
                                <span class="spinner-border text-primary"></span>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $fieldVisits->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
            <form id="approvalForm">
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
    <div class="modal-dialog modal-md">
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

                    <div id="map" style="height: 300px; display:none;" class="rounded mb-3 position-relative"></div>

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
            $('#map .map-loader').remove();
            if(userMarker && typeof userMarker.remove === 'function'){ userMarker.remove(); userMarker = null; }
            if(myMap && typeof myMap.resize === 'function') myMap.resize();
        }

        // Reject button click
        $('.reject-btn').click(function() {
            currentVisitId = $(this).data('id');
            $('#rejectionForm').attr('action', '{{ url("field-visits") }}/' + currentVisitId + '/reject');
            $('#rejectionModal').modal('show');
        });

        // Start visit button click
        $('.start-visit-btn').click(function() {
            const visitId = $(this).data('id');
            if (confirm('Are you sure you want to start this field visit?')) {
                $.post('{{ url("field-visits") }}/' + visitId + '/start', {
                    _token: '{{ csrf_token() }}'
                }).done(function(response) {
                    location.reload();
                }).fail(function(xhr) {
                    console.log(xhr);
                    alert('Error starting visit: ' + xhr.responseJSON.message);

                });
            }
        });

        // Complete visit button click
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
