@extends('layouts.app')

@section('title', 'Company Package Details')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .detail-card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .detail-section { margin-bottom: 20px; }
        .badge { font-size: 0.75rem; }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Company Package Details</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item"><a href="{{ route('superadmin.company-packages.index') }}">Company Packages</a></div>
                        <div class="breadcrumb-item active">Details</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card detail-card">
                            <div class="card-header">
                                <h4>Package Assignment Information</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('superadmin.company-packages.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to List
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-section">
                                            <h6><i class="fas fa-building"></i> Company Information</h6>
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Name:</strong></td>
                                                    <td>{{ $companyPackage->company->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td>{{ $companyPackage->company->email }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Phone:</strong></td>
                                                    <td>{{ $companyPackage->company->phone ?? 'N/A' }}</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <div class="detail-section">
                                            <h6><i class="fas fa-cube"></i> Package Information</h6>
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Package:</strong></td>
                                                    <td>{{ $companyPackage->package->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Type:</strong></td>
                                                    <td><span class="badge badge-info">{{ ucfirst($companyPackage->package->pricing_type) }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Price:</strong></td>
                                                    <td>{{ $companyPackage->package->currency }} {{ number_format($companyPackage->package->base_price, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        @if($companyPackage->package->is_active)
                                                            <span class="badge badge-success">Active</span>
                                                        @else
                                                            <span class="badge badge-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="detail-section">
                                            <h6><i class="fas fa-calendar-alt"></i> Assignment Details</h6>
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Assigned Date:</strong></td>
                                                    <td>{{ $companyPackage->assigned_at->format('M d, Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Activated Date:</strong></td>
                                                    <td>{{ $companyPackage->activated_at ? $companyPackage->activated_at->format('M d, Y') : 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Expiry Date:</strong></td>
                                                    <td>{{ $companyPackage->expires_at ? $companyPackage->expires_at->format('M d, Y') : 'No expiry' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        @if($companyPackage->is_active)
                                                            <span class="badge badge-success">Active</span>
                                                        @elseif($companyPackage->expires_at && $companyPackage->expires_at->isPast())
                                                            <span class="badge badge-danger">Expired</span>
                                                        @else
                                                            <span class="badge badge-warning">Inactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Assigned By:</strong></td>
                                                    <td>{{ $companyPackage->assignedBy->name ?? 'System' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6><i class="fas fa-chart-line"></i> Billing Information</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">Total Invoiced</h5>
                                                        <h3 class="text-primary">{{ $companyPackage->package->currency }} {{ number_format($companyPackage->invoices()->sum('total_amount'), 2) }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">Total Paid</h5>
                                                        <h3 class="text-success">{{ $companyPackage->package->currency }} {{ number_format($companyPackage->invoices()->where('status', 'paid')->sum('total_amount'), 2) }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">Pending</h5>
                                                        <h3 class="text-warning">{{ $companyPackage->package->currency }} {{ number_format($companyPackage->invoices()->where('status', 'pending')->sum('total_amount'), 2) }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title">Invoices</h5>
                                                        <h3 class="text-info">{{ $companyPackage->invoices()->count() }}</h3>
                                                    </div>
                                                </div>
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
    </div>
@endsection