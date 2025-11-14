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
                    <div class="card field-visit-card">
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
                                    <form action="{{ route('field-visits.approve', $fieldVisit) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $fieldVisit->id }}">
                                        <i class="fas fa-times"></i> Reject
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
                                        @elseif($fieldVisit->status === 'approved')
                                            <span class="badge badge-primary badge-lg">Approved</span>
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
                                    <div class="visit-info-card field-visit rounded-4 shadow-sm border-0 p-4 mb-4 position-relative overflow-hidden">
                                       <!-- Floating Title -->
                                       <div class="visit-info-title position-absolute top-0 start-0 w-100 text-center py-2">
                                          <h5 class="fw-bold mb-0 text-white">
                                          <i class="fas fa-briefcase me-2"></i>Visit Information
                                          </h5>
                                       </div>

                                       <!-- Card Body -->
                                       <div class="visit-info-content mt-5">
                                          <div class="info-item mb-3">
                                             <div class="d-flex align-items-center gap-3">
                                                <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                                   <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                   <h6 class="mb-1 fw-semibold text-dark">Employee</h6>
                                                   <p class="mb-0 text-muted">{{ $fieldVisit->employee->name }}</p>
                                                </div>
                                             </div>
                                          </div>

                                          <div class="info-item mb-3">
                                             <div class="d-flex align-items-center gap-3">
                                                <div class="icon-circle bg-success bg-opacity-10 text-success">
                                                   <i class="fas fa-user-tie"></i>
                                                </div>
                                            <div>
                                               <h6 class="mb-1 fw-semibold text-dark">Reporting Manager</h6>
                                               <p class="mb-0 text-muted">{{ $fieldVisit->reportingManager->name }}</p>
                                           </div>
                                       </div>
                                   </div>

                                  <div class="info-item">
                                     <div class="d-flex align-items-center gap-3">
                                     <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-align-left"></i>
                                     </div>
                                     <div>
                                        <h6 class="mb-1 fw-semibold text-dark">Description</h6>
                                        <p class="mb-0 text-muted">{{ $fieldVisit->visit_description ?: 'No description provided' }}</p>
                                     </div>
                                     </div>
                                  </div>
                               </div>
                           </div>
                           </div>

                                <!-- Location Information -->
                                <div class="col-md-6">
                                <div class="card field-visit location-info glass-card rounded-4 shadow-sm border-0 p-4 mb-4 position-relative overflow-hidden">
                                <!-- Floating Title -->
                                <div class="visit-info-title position-absolute top-0 start-0 w-100 text-center py-2">
                                   <h5 class="fw-bold mb-0 text-white">
                                   <i class="fas fa-map-marker-alt me-2"></i>Location Details
                                   </h5>
                                </div>

                                <!-- Card Body -->
                                <div class="location-info-content mt-5">
                                   <div class="info-item mb-3">
                                      <div class="d-flex align-items-center gap-3">
                                         <div class="icon-circle bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-location-dot"></i>
                                        </div>
                                        <div>
                                        <h6 class="mb-1 fw-semibold text-dark">Location Name</h6>
                                        <p class="mb-0 text-muted">{{ $fieldVisit->location_name }}</p>
                                        </div>
                                     </div>
                                   </div>

                                  <div class="info-item mb-3">
                                     <div class="d-flex align-items-center gap-3">
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                           <i class="fas fa-map"></i>
                                        </div>
                                    <div>
                                       <h6 class="mb-1 fw-semibold text-dark">Address</h6>
                                       <p class="mb-0 text-muted">{{ $fieldVisit->location_address }}</p>
                                    </div>
                               </div>
                           </div>

                           @if($fieldVisit->latitude && $fieldVisit->longitude)
                          <div class="info-item">
                             <div class="d-flex align-items-center gap-3">
                                <div class="icon-circle bg-success bg-opacity-10 text-success">
                                   <i class="fas fa-compass"></i>
                                </div>
                                <div>
                                   <h6 class="mb-1 fw-semibold text-dark">Coordinates</h6>
                                   <p class="mb-1 text-muted">
                                   {{ $fieldVisit->latitude }}, {{ $fieldVisit->longitude }}
                                   </p>
                                   <a href="https://www.google.com/maps?q={{ $fieldVisit->latitude }},{{ $fieldVisit->longitude }}"
                                    target="_blank" class="btn btn-glass btn-sm btn-outline-info">
                                   <i class="fas fa-map-marker-alt me-1"></i> View on Map
                                   </a>
                                </div>
                             </div>
                          </div>
                          @endif
                      </div>
                  </div>
              </div>
          </div>

          <div class="row mt-3">
             <!-- Schedule Information -->
             <div class="col-md-6">
                                  
                <div class="card field-visit schedule-info glass-card rounded-4 shadow-sm border-0 p-4 mb-4 position-relative overflow-hidden">
                   <!-- Floating Title -->
                   <div class="visit-info-title position-absolute top-0 start-0 w-100 text-center py-2">
                      <h5 class="fw-bold mb-0 text-white">
                      <i class="fas fa-clock me-2"></i>Schedule
                      </h5>
                   </div>

                   <!-- Card Body -->
                   <div class="schedule-info-content mt-5">
                      <div class="info-item mb-3">
                         <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                               <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold text-dark">Scheduled Start</h6>
                                <p class="mb-0 text-muted">
                                {{ $fieldVisit->scheduled_start_datetime->format('M d, Y H:i') }}
                                </p>
                            </div>
                         </div>
                      </div>

                      <div class="info-item">
                         <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                               <i class="fas fa-calendar-check"></i>
                            </div>
                                <div>
                                <h6 class="mb-1 fw-semibold text-dark">Scheduled End</h6>
                                <p class="mb-0 text-muted">
                                {{ $fieldVisit->scheduled_end_datetime->format('M d, Y H:i') }}
                                </p>
                                </div>
                      </div>
              </div>

              @if($fieldVisit->approved_at && $fieldVisit->approvedBy)
                  <div class="info-item mt-3">
                      <div class="d-flex align-items-center gap-3">
                          <div class="icon-circle bg-success bg-opacity-10 text-success">
                              <i class="fas fa-user-check"></i>
                          </div>
                          <div>
                              <h6 class="mb-1 fw-semibold text-dark">Approved By</h6>
                              <p class="mb-0 text-muted">
                                  {{ $fieldVisit->approvedBy->name }} 
                                  {{-- <br><small>on {{ $fieldVisit->approved_at->format('M d, Y H:i') }}</small> --}}
                              </p>
                          </div>
                      </div>
                  </div>
              @endif
          </div>
      </div>
      </div>
      
      <!-- Visit Notes & attachments -->
          <div class="col-md-6">
              <div class="card field-visit visit-details glass-card rounded-4 shadow-sm border-0 p-4 mb-4 position-relative overflow-hidden">
              <!-- Floating Title -->
              <div class="visit-info-title  position-absolute top-0 start-0 w-100 text-center py-2">
              <h5 class="fw-bold mb-0 text-white">
                  <i class="fas fa-clipboard-list me-2"></i>Visit Details
              </h5>
              </div>
              <!-- Body Content -->
              <div class="visit-details-content mt-5">
                 @if($fieldVisit->visit_notes)
                  <div class="info-item mb-3">
                      <div class="d-flex align-items-start gap-3">
                          <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                              <i class="fas fa-sticky-note"></i>
                          </div>
                          <div>
                              <h6 class="mb-1 fw-semibold text-dark">Visit Notes</h6>
                              <p class="mb-0 text-muted">{{ $fieldVisit->visit_notes }}</p>
                          </div>
                      </div>
                  </div>
              @endif

              @if($fieldVisit->manager_feedback)
                  <div class="info-item mb-3">
                      <div class="d-flex align-items-start gap-3">
                          <div class="icon-circle bg-info bg-opacity-10 text-info">
                              <i class="fas fa-comments"></i>
                          </div>
                          <div>
                              <h6 class="mb-1 fw-semibold text-dark">Manager Feedback</h6>
                              <div class="alert alert-info mt-1 mb-0 py-2 px-3 rounded-3">
                                  {{ $fieldVisit->manager_feedback }}
                              </div>
                          </div>
                      </div>
                  </div>
              @endif

              @if($fieldVisit->visit_attachments && count($fieldVisit->visit_attachments) > 0)
                  <div class="info-item mb-3">
                      <div class="d-flex align-items-start gap-3">
                          <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                              <i class="fas fa-image"></i>
                          </div>
                          <div>
                              <h6 class="mb-1 fw-semibold text-dark">Visit Photos</h6>
                              <div class="d-flex flex-wrap gap-2 mt-1">
                                  @foreach($fieldVisit->visit_attachments as $attachment)
                                      <a href="{{ Storage::url($attachment) }}" target="_blank" 
                                          class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                          <i class="fas fa-file-image me-1"></i>{{ basename($attachment) }}
                                      </a>
                                  @endforeach
                              </div>
                          </div>
                      </div>
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
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    let currentVisitId = {{ $fieldVisit->id }};

    // Reject button click
    $('.reject-btn').click(function () {
        $('#rejectionForm').attr('action', '{{ url("field-visits") }}/' + currentVisitId + '/reject');
        $('#rejectionModal').modal('show');
    });

    // Auto dismiss alerts after 3 sec
    setTimeout(() => {
        $('#successAlert, #errorAlert, #validationAlert').alert('close');
    }, 3000);
});
</script>
@endpush
