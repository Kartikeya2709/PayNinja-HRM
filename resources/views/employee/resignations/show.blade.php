@extends('layouts.app')

@section('title', 'Resignation Details')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Resignation Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('resignations.index') }}">Resignations</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Resignation Information</h5>
                        <div class="card-header-action">
                            <a href="{{ route('resignations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
  
    <div class="resignation-summary">

    <!-- Header -->
    <div class="resignation-header">
        <h2><i class="fas fa-user-slash me-2 text-primary"></i> Resignation Summary</h2>
        <p>Your resignation request overview and exit process details</p>
        <div class="mt-2">
            <span class="status-badge status-{{ $resignation->status }}">
                {{ ucfirst($resignation->status_label) }}
            </span>
        </div>
    </div>

    <!-- Info -->
    <div class="info-grid">
        <div class="info-block">
            <label><i class="fas fa-file-alt"></i> Resignation Type</label>
            <span>{{ $resignation->resignation_type_label }}</span>
        </div>
        <div class="info-block">
            <label><i class="fas fa-calendar"></i> Resignation Date</label>
            <span>{{ $resignation->resignation_date->format('M d, Y') }}</span>
        </div>
        <div class="info-block">
            <label><i class="fas fa-calendar-check"></i> Last Working Date</label>
            <span>{{ $resignation->last_working_date->format('M d, Y') }}</span>
        </div>
        <div class="info-block">
            <label><i class="fas fa-hourglass-half"></i> Notice Period</label>
            <span>{{ $resignation->notice_period_days }} days</span>
        </div>
    </div>

    <!-- Timeline -->
    <div class="timeline">
        <div class="timeline-line"></div>
        <div class="timeline-steps">

            <!-- Exit Interview -->
            <div class="timeline-step">
                @php $status = $resignation->exit_interview_status ?? 'pending'; @endphp
                <div class="circle {{ $status }}">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="label">Exit Interview</div>
                <div class="step-status {{ $status }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </div>
            </div>

            <!-- Handover -->
            <div class="timeline-step">
                @php $status = $resignation->handover_status ?? 'pending'; @endphp
                <div class="circle {{ $status }}">
                    <i class="fas fa-people-arrows"></i>
                </div>
                <div class="label">Handover</div>
                <div class="step-status {{ $status }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </div>
            </div>

            <!-- Assets Returned -->
            <div class="timeline-step">
                @php $status = $resignation->assets_status ?? 'pending'; @endphp
                <div class="circle {{ $status }}">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="label">Assets Returned</div>
                <div class="step-status {{ $status }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </div>
            </div>

            <!-- Final Settlement -->
            <div class="timeline-step">
                @php $status = $resignation->settlement_status ?? 'pending'; @endphp
                <div class="circle {{ $status }}">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="label">Final Settlement</div>
                <div class="step-status {{ $status }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Remarks -->
    <div class="remarks">
        @if($resignation->reason)
            <h5><i class="fas fa-comment-dots text-primary"></i> Reason for Resignation</h5>
            <p>{{ $resignation->reason }}</p>
        @endif
        @if($resignation->employee_remarks)
            <h5><i class="fas fa-user-pen text-primary"></i> Your Remarks</h5>
            <p>{{ $resignation->employee_remarks }}</p>
        @endif
        @if($resignation->manager_remarks)
            <h5><i class="fas fa-user-tie text-success"></i> Manager Remarks</h5>
            <p>{{ $resignation->manager_remarks }}</p>
        @endif
        @if($resignation->hr_remarks)
            <h5><i class="fas fa-user-cog text-info"></i> HR Remarks</h5>
            <p>{{ $resignation->hr_remarks }}</p>
        @endif
        @if($resignation->admin_remarks)
            <h5><i class="fas fa-user-shield text-danger"></i> Admin Remarks</h5>
            <p>{{ $resignation->admin_remarks }}</p>
        @endif
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        @if($resignation->canBeWithdrawn())
            <button type="button" class="btn btn-danger" onclick="withdrawResignation({{ $resignation->id }})">
                <i class="fas fa-times"></i> Withdraw Resignation
            </button>
        @endif

        @if($resignation->status === 'pending')
            <a href="{{ route('resignations.edit', $resignation) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Request
            </a>
        @endif
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
function withdrawResignation(resignationId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to withdraw this resignation request? This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, withdraw it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/employee/resignations/${resignationId}/withdraw`;

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush