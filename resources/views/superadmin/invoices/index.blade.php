@extends('layouts.app')

@section('title', 'Invoice Management')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .table-responsive { overflow-x: auto; }
        .invoice-amount {
            font-weight: bold;
            color: #28a745;
        }
        .status-pending { color: orange; }
        .status-paid { color: green; }
        .status-overdue { color: red; }
        .status-cancelled { color: gray; }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Invoice Management</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item active">Invoices</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>All Invoices</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('superadmin.invoices.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Generate Invoice
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <form method="GET" action="{{ route('superadmin.invoices.index') }}">
                                            <div class="input-group">
                                                <input type="text" name="search" class="form-control" placeholder="Search invoices..." value="{{ request('search') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-4">
                                        <form method="GET" action="{{ route('superadmin.invoices.index') }}">
                                            <div class="input-group">
                                                <select name="status" class="form-control">
                                                    <option value="">All Status</option>
                                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-4">
                                        <form method="GET" action="{{ route('superadmin.invoices.index') }}">
                                            <div class="input-group">
                                                <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                                                <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Company</th>
                                                <th>Package</th>
                                                <th>Amount</th>
                                                <th>Issue Date</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($invoices ?? [] as $invoice)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $invoice->invoice_number }}</strong>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $invoice->company->name }}</strong><br>
                                                            <small class="text-muted">{{ $invoice->company->email }}</small>
                                                        </div>
                                                    </td>
                                                    <td>{{ $invoice->package->name }}</td>
                                                    <td><span class="invoice-amount">₹ {{ number_format($invoice->total_amount, 2) }}</span></td>
                                                    <td>{{ $invoice->issue_date->format('M d, Y') }}</td>
                                                    <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                                                    <td>
                                                        @php
                                                            $statusClass = match($invoice->status) {
                                                                'pending' => 'warning',
                                                                'paid' => 'success',
                                                                'overdue' => 'danger',
                                                                'cancelled' => 'secondary',
                                                                default => 'secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge badge-{{ $statusClass }}">
                                                            {{ ucfirst($invoice->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('superadmin.invoices.show', $invoice) }}" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            @if($invoice->status == 'pending')
                                                                <form action="{{ route('superadmin.invoices.mark-paid', $invoice) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this invoice as paid?')">
                                                                        <i class="fas fa-check"></i> Mark Paid
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <a href="{{ route('superadmin.invoices.download', $invoice) }}" class="btn btn-sm btn-secondary">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No invoices found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if(isset($invoices) && $invoices->hasPages())
                                    <div class="d-flex justify-content-center">
                                        {{ $invoices->appends(request()->query())->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-file-invoice-dollar fa-2x text-primary"></i>
                                </div>
                                <h6>Total Invoices</h6>
                                <h4>{{ $invoiceStats['total'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                                <h6>Pending</h6>
                                <h4>{{ $invoiceStats['pending'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                                <h6>Paid</h6>
                                <h4>{{ $invoiceStats['paid'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="fas fa-dollar-sign fa-2x text-info"></i>
                                </div>
                                <h6>Total Revenue</h6>
                                <h4>₹ {{ number_format($invoiceStats['total_revenue'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Any additional JS for filtering or bulk actions can be added here
        });
    </script>
@endpush