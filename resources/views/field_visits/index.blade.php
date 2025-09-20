@extends('layouts.app')
@section('title', 'Field Visits')

@section('content')
    <div class="main-content-01 container">
        <section class="section">
            <div class="section-header">
                <h1>Field Visits</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active">Field Visits</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Field Visits List</h4>
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
                                        <p>You have field visit requests waiting for your approval. <a
                                                class="text-decoration-underline"
                                                href="{{ route('field-visits.pending') }}">View Pending Approvals</a></p>
                                    </div>
                                @endif

                                <!-- Filters -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select class="form-control" id="statusFilter">
                                            <option value="">All Status</option>
                                            <option value="scheduled">Scheduled</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="approvalFilter">
                                            <option value="">All Approvals</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" id="dateFrom" placeholder="From Date">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" id="dateTo" placeholder="To Date">
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
                                                    <td>{{ $visit->scheduled_start_datetime->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        @if($visit->status === 'scheduled')
                                                            <span class="badge badge-info">Scheduled</span>
                                                        @elseif($visit->status === 'in_progress')
                                                            <span class="badge badge-warning">In Progress</span>
                                                        @elseif($visit->status === 'completed')
                                                            <span class="badge badge-success">Completed</span>
                                                        @else
                                                            <span class="badge badge-secondary">Cancelled</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($visit->approval_status === 'pending')
                                                            <span class="badge badge-warning">Pending</span>
                                                        @elseif($visit->approval_status === 'approved')
                                                            <span class="badge badge-success">Approved</span>
                                                        @else
                                                            <span class="badge badge-danger">Rejected</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('field-visits.show', $visit) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
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
                                                                class="btn btn-sm btn-warning">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif

                                                        @if($canApprove)
                                                            <form action="{{ route('field-visits.approve', $visit) }}" method="POST"
                                                                style="display: inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success">
                                                                    <i class="fa-solid fa-check"></i>
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('field-visits.reject', $visit) }}" method="POST"
                                                                style="display: inline;">
                                                                @csrf
                                                                <button class="btn btn-sm btn-danger" type="submit">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if($canStart)
                                                            <button class="btn btn-sm btn-primary start-visit-btn"
                                                                data-id="{{ $visit->id }}">
                                                                <i class="fas fa-play"></i> Start
                                                            </button>
                                                        @endif

                                                        @if($canComplete)
                                                            <button class="btn btn-sm btn-success complete-visit-btn"
                                                                data-id="{{ $visit->id }}">
                                                                <i class="fas fa-stop"></i> Complete
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No field visits found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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
    <div class="modal fade" id="completionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Field Visit</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" id="completionForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="visit_notes">Visit Notes</label>
                            <textarea class="form-control" id="visit_notes" name="visit_notes" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="visit_attachments">Visit attachments</label>
                            <input type="file" class="form-control" id="visit_attachments" name="visit_attachments[]"
                                multiple accept="image/*">
                            <small class="form-text text-muted">You can upload multiple attachments (max 20MB each)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Complete Visit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let currentVisitId = null;

            // Reject button click
            $('.reject-btn').click(function () {
                currentVisitId = $(this).data('id');
                $('#rejectionForm').attr('action', '{{ url("field-visits") }}/' + currentVisitId + '/reject');
                $('#rejectionModal').modal('show');
            });

            // Start visit button click
            $('.start-visit-btn').click(function () {
                const visitId = $(this).data('id');
                if (confirm('Are you sure you want to start this field visit?')) {
                    $.post('{{ url("field-visits") }}/' + visitId + '/start', {
                        _token: '{{ csrf_token() }}'
                    }).done(function (response) {
                        location.reload();
                    }).fail(function (xhr) {
                        console.log(xhr);
                        alert('Error starting visit: ' + xhr.responseJSON.message);

                    });
                }
            });

            // Complete visit button click
            $('.complete-visit-btn').click(function () {
                currentVisitId = $(this).data('id');
                $('#completionForm').attr('action', '{{ url("field-visits") }}/' + currentVisitId + '/complete');
                $('#completionModal').modal('show');
            });

            // Filter functionality
            $('#statusFilter, #approvalFilter, #dateFrom, #dateTo').change(function () {
                const status = $('#statusFilter').val();
                const approval = $('#approvalFilter').val();
                const dateFrom = $('#dateFrom').val();
                const dateTo = $('#dateTo').val();

                let url = '{{ route("field-visits.index") }}?';
                if (status) url += 'status=' + status + '&';
                if (approval) url += 'approval_status=' + approval + '&';
                if (dateFrom) url += 'date_from=' + dateFrom + '&';
                if (dateTo) url += 'date_to=' + dateTo + '&';

                window.location.href = url.slice(0, -1);
            });
        });
    </script>
@endpush