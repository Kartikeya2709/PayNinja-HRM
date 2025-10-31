@extends('layouts.app')

@section('title', 'Edit Package')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .module-item {
            cursor: pointer;
            padding: 10px;
            border: 1px solid #ddd;
            margin: 5px 0;
            border-radius: 5px;
        }
        .module-item.selected {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .pricing-tier {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .remove-tier {
            color: red;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Edit Package</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item"><a href="{{ route('superadmin.packages.index') }}">Packages</a></div>
                        <div class="breadcrumb-item active">Edit</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Package Information</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('superadmin.packages.update', $package) }}" method="POST" id="packageForm">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Package Name</label>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $package->name) }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pricing_type">Pricing Type</label>
                                                <select name="pricing_type" id="pricing_type" class="form-control @error('pricing_type') is-invalid @enderror" required>
                                                    <option value="">Select Pricing Type</option>
                                                    <option value="one-time" {{ old('pricing_type', $package->pricing_type) == 'one-time' ? 'selected' : '' }}>One-time</option>
                                                    <option value="recurring" {{ old('pricing_type', $package->pricing_type) == 'recurring' ? 'selected' : '' }}>Recurring</option>
                                                </select>
                                                @error('pricing_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="base_price">Base Price</label>
                                                <input type="number" step="0.01" name="base_price" id="base_price" class="form-control @error('base_price') is-invalid @enderror" value="{{ old('base_price', $package->base_price) }}" required>
                                                @error('base_price')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="currency">Currency</label>
                                                <select name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required>
                                                    <option value="USD" {{ old('currency', $package->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                                    <option value="EUR" {{ old('currency', $package->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                                    <option value="GBP" {{ old('currency', $package->currency) == 'GBP' ? 'selected' : '' }}>GBP</option>
                                                    <option value="INR" {{ old('currency', $package->currency) == 'INR' ? 'selected' : '' }}>INR</option>
                                                </select>
                                                @error('currency')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $package->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group" id="billing_cycle_group" style="{{ old('pricing_type', $package->pricing_type) == 'recurring' ? '' : 'display: none;' }}">
                                        <label for="billing_cycle">Billing Cycle</label>
                                        <select name="billing_cycle" id="billing_cycle" class="form-control @error('billing_cycle') is-invalid @enderror">
                                            <option value="">Select Billing Cycle</option>
                                            <option value="monthly" {{ old('billing_cycle', $package->billing_cycle) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="yearly" {{ old('billing_cycle', $package->billing_cycle) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        </select>
                                        @error('billing_cycle')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Modules</label>
                                        <div id="modules-list">
                                            @if($slugs->count() > 0)
                                                @include('superadmin.packages.partials.slug-tree', [
                                                    'slugs' => $slugs,
                                                    'selectedSlugs' => old('modules', $package->packageModules->pluck('module_name')->toArray()),
                                                    'level' => 0
                                                ])
                                            @else
                                                <p class="text-muted">No modules available. Please create slugs first.</p>
                                            @endif
                                        </div>
                                        @error('modules')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Pricing Tiers</label>
                                        <div id="pricing-tiers">
                                            @php
                                                $existingTiers = old('pricing_tiers', $package->pricingTiers->toArray());
                                            @endphp
                                            @foreach($existingTiers as $index => $tier)
                                                <div class="pricing-tier">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <input type="text" name="pricing_tiers[{{ $index }}][name]" class="form-control" placeholder="Tier Name" value="{{ $tier['name'] ?? '' }}" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" name="pricing_tiers[{{ $index }}][min_users]" class="form-control" placeholder="Min Users" value="{{ $tier['min_users'] ?? '' }}" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" step="0.01" name="pricing_tiers[{{ $index }}][price]" class="form-control" placeholder="Price" value="{{ $tier['price'] ?? '' }}" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <button type="button" class="btn btn-danger remove-tier">Remove</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" id="add-tier" class="btn btn-secondary mt-2">Add Pricing Tier</button>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Update Package</button>
                                        <a href="{{ route('superadmin.packages.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            const pricingTypeSelect = document.getElementById('pricing_type');
            const billingCycleGroup = document.getElementById('billing_cycle_group');

            pricingTypeSelect.addEventListener('change', function() {
                if (this.value === 'recurring') {
                    billingCycleGroup.style.display = 'block';
                } else {
                    billingCycleGroup.style.display = 'none';
                }
            });

            // Module selection
            document.querySelectorAll('.module-item').forEach(item => {
                item.addEventListener('click', function() {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    this.classList.toggle('selected', checkbox.checked);
                });
            });

            // Pricing tiers
            let tierIndex = {{ count(old('pricing_tiers', $package->pricingTiers ?? [])) }};
            document.getElementById('add-tier').addEventListener('click', function() {
                const tiersContainer = document.getElementById('pricing-tiers');
                const tierHtml = `
                    <div class="pricing-tier">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="pricing_tiers[${tierIndex}][name]" class="form-control" placeholder="Tier Name" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="pricing_tiers[${tierIndex}][min_users]" class="form-control" placeholder="Min Users" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" name="pricing_tiers[${tierIndex}][price]" class="form-control" placeholder="Price" required>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-danger remove-tier">Remove</button>
                            </div>
                        </div>
                    </div>
                `;
                tiersContainer.insertAdjacentHTML('beforeend', tierHtml);
                tierIndex++;
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-tier')) {
                    e.target.closest('.pricing-tier').remove();
                }
            });
        });
    </script>
@endpush