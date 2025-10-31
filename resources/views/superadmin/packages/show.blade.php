@extends('layouts.app')

@section('title', 'Package Details')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .detail-row {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .status-active { color: green; }
        .status-inactive { color: red; }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Package Details</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item"><a href="{{ route('superadmin.packages.index') }}">Packages</a></div>
                        <div class="breadcrumb-item active">{{ $package->name }}</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Package Information</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('superadmin.packages.edit', $package) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('superadmin.packages.destroy', $package) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this package?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-row">
                                            <span class="detail-label">Name:</span> {{ $package->name }}
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Pricing Type:</span> {{ ucfirst($package->pricing_type) }}
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Base Price:</span> {{ $package->currency }} {{ number_format($package->base_price, 2) }}
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Status:</span>
                                            <span class="badge badge-{{ $package->is_active ? 'success' : 'danger' }}">
                                                {{ $package->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @if($package->pricing_type == 'recurring')
                                            <div class="detail-row">
                                                <span class="detail-label">Billing Cycle:</span> {{ ucfirst($package->billing_cycle) }}
                                            </div>
                                        @endif
                                        <div class="detail-row">
                                            <span class="detail-label">Created:</span> {{ $package->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Last Updated:</span> {{ $package->updated_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Description:</span>
                                    <p>{{ $package->description ?: 'No description provided.' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Included Modules</h4>
                            </div>
                            <div class="card-body">
                                @if($package->modules && $package->modules->count() > 0)
                                    <div class="row">
                                        @foreach($package->modules as $module)
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-cube fa-2x text-primary"></i>
                                                        <h6 class="mt-2">{{ $module->name }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p>No modules assigned to this package.</p>
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Pricing Tiers</h4>
                            </div>
                            <div class="card-body">
                                @if($package->pricingTiers && $package->pricingTiers->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Tier Name</th>
                                                    <th>Min Users</th>
                                                    <th>Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($package->pricingTiers as $tier)
                                                    <tr>
                                                        <td>{{ $tier->name }}</td>
                                                        <td>{{ $tier->min_users }}</td>
                                                        <td>{{ $package->currency }} {{ number_format($tier->price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p>No pricing tiers defined.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Assigned Companies</h4>
                            </div>
                            <div class="card-body">
                                @if($package->companies && $package->companies->count() > 0)
                                    <div class="list-group">
                                        @foreach($package->companies as $company)
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">{{ $company->name }}</h6>
                                                    <small>{{ $company->pivot->assigned_at->format('M d, Y') }}</small>
                                                </div>
                                                <p class="mb-1">{{ $company->email }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p>No companies assigned to this package.</p>
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('superadmin.packages.edit', $package) }}" class="btn btn-warning btn-block mb-2">
                                    <i class="fas fa-edit"></i> Edit Package
                                </a>
                                <a href="{{ route('superadmin.company-packages.assign', ['package' => $package->id]) }}" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-plus"></i> Assign to Company
                                </a>
                                <form action="{{ route('superadmin.packages.toggle-active', $package) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-{{ $package->is_active ? 'danger' : 'success' }} btn-block">
                                        <i class="fas fa-{{ $package->is_active ? 'times' : 'check' }}"></i>
                                        {{ $package->is_active ? 'Deactivate' : 'Activate' }} Package
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection