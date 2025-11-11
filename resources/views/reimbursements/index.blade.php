@extends('layouts.app')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Reimbursements</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Reimbursements</a></div>
            </div>
        </div>

        <div class="card">
            <div class="card-1">
                <h5 class="card-title margin-bottom mb-0">Reimbursements</h5>
            </div>

            <!-- Filters -->
            <div class="row mt-3 mb-4">
                <div class="col-lg-3 col-md-4 mb-3">
                    <select class="form-control" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="reporter_approved">Reporter Approved</option>
                        <option value="admin_approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 mb-3">
                    <input type="date" class="form-control" id="dateFromFilter" placeholder="From Date">
                </div>
                <div class="col-lg-3 col-md-4 mb-3">
                    <input type="date" class="form-control" id="dateToFilter" placeholder="To Date">
                </div>
                <div class="col-lg-3 col-md-4 mb-3">
                    <input type="number" class="form-control" id="minAmountFilter" placeholder="Min Amount" step="0.01">
                </div>
                <div class="col-lg-3 col-md-4 mb-3">
                    <input type="number" class="form-control" id="maxAmountFilter" placeholder="Max Amount" step="0.01">
                </div>
                <div class="col-lg-3 col-md-4">
                    <button type="button" class="btn btn-secondary w-100" id="clearFilters">Clear Filters</button>
                </div>
            </div>
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif



            <div class="Reimburs-table">
                <table class="table table-bordered Reimbursements-table">
                    <thead>
                        <tr>
                            <th>Serial No.</th>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Employee</th>
                            <th>Company</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="reimbursementTable">
                        @foreach($reimbursements as $reimbursement)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d M Y') }}</td>
                            <td>{{ $reimbursement->title }}</td>
                            <td>{{ $reimbursement->employee->user->name }}</td>
                            <td>{{ $reimbursement->company->name }}</td>
                            <td>₹{{ number_format($reimbursement->amount, 2) }}</td>
                            <td>
                                <span
                                    class="badge bg-{{ $reimbursement->status === 'pending' ? 'warning' : ($reimbursement->status === 'reporter_approved' ? 'info' : ($reimbursement->status === 'admin_approved' ? 'success' : 'danger')) }}">
                                    {{ ucfirst($reimbursement->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('reimbursements.show', $reimbursement->id) }}"
                                    class="btn btn-outline-primary action-btn"
                                    data-id="{{ $reimbursement->id }}" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="View Details" aria-label="View">
                                    <span class="btn-content">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true"></span>
                                </a>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-3">
                    {{ $reimbursements->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
</div>

<script>
$(document).ready(function() {
    // Set current filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    $('#statusFilter').val(urlParams.get('status') || '');
    $('#dateFromFilter').val(urlParams.get('date_from') || '');
    $('#dateToFilter').val(urlParams.get('date_to') || '');
    $('#minAmountFilter').val(urlParams.get('min_amount') || '');
    $('#maxAmountFilter').val(urlParams.get('max_amount') || '');

    // Filter functionality
    $('#statusFilter, #dateFromFilter, #dateToFilter, #minAmountFilter, #maxAmountFilter').change(function() {
        applyFilters();
    });

    // Clear filters
    $('#clearFilters').click(function() {
        $('#statusFilter').val('');
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');
        $('#minAmountFilter').val('');
        $('#maxAmountFilter').val('');
        applyFilters();
    });

    function applyFilters() {
        const status = $('#statusFilter').val();
        const dateFrom = $('#dateFromFilter').val();
        const dateTo = $('#dateToFilter').val();
        const minAmount = $('#minAmountFilter').val();
        const maxAmount = $('#maxAmountFilter').val();

        let url = '{{ route("reimbursements.index") }}?';
        if (status) url += 'status=' + status + '&';
        if (dateFrom) url += 'date_from=' + dateFrom + '&';
        if (dateTo) url += 'date_to=' + dateTo + '&';
        if (minAmount) url += 'min_amount=' + minAmount + '&';
        if (maxAmount) url += 'max_amount=' + maxAmount + '&';

        window.location.href = url.slice(0, -1);
    }
});
</script>
@endsection