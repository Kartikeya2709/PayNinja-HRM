@extends('layouts.app')

@section('title', 'Invoice Details')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .invoice-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .invoice-details {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 5px 10px;
        }
        .line-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .total-row {
            font-weight: bold;
            font-size: 1.1em;
            border-top: 2px solid #dee2e6;
            padding-top: 10px;
        }
        .company-info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Invoice Details</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item"><a href="{{ route('superadmin.invoices.index') }}">Invoices</a></div>
                        <div class="breadcrumb-item active">{{ $invoice->invoice_number }}</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Invoice #{{ $invoice->invoice_number }}</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('superadmin.invoices.download', $invoice) }}" class="btn btn-primary">
                                        <i class="fas fa-download"></i> Download PDF
                                    </a>
                                    <a href="{{ route('superadmin.invoices.print', $invoice) }}" target="_blank" class="btn btn-secondary">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                    @if($invoice->status == 'pending')
                                        <form action="{{ route('superadmin.invoices.mark-paid', $invoice) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Mark this invoice as paid?')">
                                                <i class="fas fa-check"></i> Mark as Paid
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="invoice-header">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Invoice Information</h5>
                                            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                                            <p><strong>Issue Date:</strong> {{ $invoice->issue_date->format('M d, Y') }}</p>
                                            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                                            <p><strong>Status:</strong>
                                                @php
                                                    $statusClass = match($invoice->status) {
                                                        'pending' => 'warning',
                                                        'paid' => 'success',
                                                        'overdue' => 'danger',
                                                        'cancelled' => 'secondary',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge badge-{{ $statusClass }} status-badge">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Package Details</h5>
                                            <p><strong>Package:</strong> {{ $invoice->package->name }}</p>
                                            <p><strong>Pricing Type:</strong> {{ ucfirst($invoice->package->pricing_type) }}</p>
                                            @if($invoice->package->pricing_type == 'recurring')
                                                <p><strong>Billing Cycle:</strong> {{ ucfirst($invoice->package->billing_cycle) }}</p>
                                            @endif
                                            <p><strong>Currency:</strong> INR</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="company-info mb-4">
                                    <h5>Bill To</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Company:</strong> {{ $invoice->company->name }}</p>
                                            <p><strong>Email:</strong> {{ $invoice->company->email }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Phone:</strong> {{ $invoice->company->phone ?? 'N/A' }}</p>
                                            <p><strong>Address:</strong> {{ $invoice->company->address ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="invoice-details">
                                    <h5>Invoice Items</h5>
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th class="text-right">Quantity</th>
                                                    <th class="text-right">Unit Price</th>
                                                    <th class="text-right">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($invoice->lineItems && $invoice->lineItems->count() > 0)
                                                    @foreach($invoice->lineItems as $item)
                                                        <tr class="line-item">
                                                            <td>{{ $item->description }}</td>
                                                            <td class="text-right">{{ $item->quantity }}</td>
                                                            <td class="text-right">₹ {{ number_format($item->unit_price, 2) }}</td>
                                                            <td class="text-right">₹ {{ number_format($item->amount, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr class="line-item">
                                                        <td>{{ $invoice->package->name }} Package</td>
                                                        <td class="text-right">1</td>
                                                        <td class="text-right">₹ {{ number_format($invoice->subtotal, 2) }}</td>
                                                        <td class="text-right">₹ {{ number_format($invoice->subtotal, 2) }}</td>
                                                    </tr>
                                                @endif

                                                <tr class="total-row">
                                                    <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                                    <td class="text-right">₹ {{ number_format($invoice->subtotal, 2) }}</td>
                                                </tr>

                                                @if($invoice->discount_amount > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-right">Discount:</td>
                                                        <td class="text-right">-₹ {{ number_format($invoice->discount_amount, 2) }}</td>
                                                    </tr>
                                                @endif

                                                @if($invoice->tax_amount > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-right">Tax:</td>
                                                        <td class="text-right">₹ {{ number_format($invoice->tax_amount, 2) }}</td>
                                                    </tr>
                                                @endif

                                                <tr class="total-row">
                                                    <td colspan="3" class="text-right"><strong>Total Amount:</strong></td>
                                                    <td class="text-right"><strong>₹ {{ number_format($invoice->total_amount, 2) }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                @if($invoice->notes)
                                    <div class="mt-4">
                                        <h6>Notes:</h6>
                                        <p>{{ $invoice->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Payment Information</h4>
                            </div>
                            <div class="card-body">
                                @if($invoice->paid_at)
                                    <p><strong>Payment Date:</strong> {{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                                    <p><strong>Payment Method:</strong> {{ $invoice->payment_method ?? 'N/A' }}</p>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> This invoice has been paid.
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-clock"></i> Payment is pending.
                                    </div>
                                    @if($invoice->due_date->isPast() && $invoice->status != 'cancelled')
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle"></i> This invoice is overdue.
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('superadmin.invoices.download', $invoice) }}" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-download"></i> Download PDF
                                </a>
                                <a href="mailto:{{ $invoice->company->email }}?subject=Invoice {{ $invoice->invoice_number }}" class="btn btn-info btn-block mb-2">
                                    <i class="fas fa-envelope"></i> Email Invoice
                                </a>
                                @if($invoice->status == 'pending')
                                    <form action="{{ route('superadmin.invoices.mark-paid', $invoice) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Mark this invoice as paid?')">
                                            <i class="fas fa-check"></i> Mark as Paid
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if($invoice->package->modules && $invoice->package->modules->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h4>Package Modules</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        @foreach($invoice->package->modules as $module)
                                            <li class="list-group-item">
                                                <i class="fas fa-cube text-primary"></i> {{ $module->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection