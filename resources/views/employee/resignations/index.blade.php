@extends('layouts.app')

@section('title', 'My Resignation Requests')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>My Resignation Requests</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Resignation Requests</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">My Resignation Requests</h5>
                <div class="card-header-action">
                    <a href="{{ route('employee.resignations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Submit Resignation
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped" id="resignationsTable">
                        <thead>
                            <tr>
                                <th>Resignation Type</th>
                                <th>Resignation Date</th>
                                <th>Last Working Date</th>
                                <th>Notice Period</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resignations as $resignation)
                                <tr>
                                    <td>
                                        <span class="badge badge-info">{{ $resignation->resignation_type_label }}</span>
                                    </td>
                                    <td>{{ $resignation->resignation_date->format('M d, Y') }}</td>
                                    <td>{{ $resignation->last_working_date->format('M d, Y') }}</td>
                                    <td>{{ $resignation->notice_period_days }} days</td>
                                    <td>
                                        <span class="badge badge-{{ $resignation->status_color }}">
                                            {{ $resignation->status_label }}
                                        </span>
                                        @if($resignation->remaining_days !== null && $resignation->remaining_days > 0)
                                            <br><small class="text-muted">{{ $resignation->remaining_days }} days remaining</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('employee.resignations.show', $resignation) }}"
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if($resignation->canBeWithdrawn())
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="withdrawResignation({{ $resignation->id }})">
                                                <i class="fas fa-times"></i> Withdraw
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                                            <h6>No resignation requests found</h6>
                                            <p class="text-muted">You haven't submitted any resignation requests yet.</p>
                                            <a href="{{ route('employee.resignations.create') }}" class="btn btn-primary">
                                                Submit Your First Resignation
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#resignationsTable').DataTable({
        order: [[1, 'desc']],
        pageLength: 25
    });
});

function withdrawResignation(resignationId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to withdraw this resignation request?",
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