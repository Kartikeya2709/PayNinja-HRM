@extends('layouts.app')

@section('title', 'Assign Package to Company')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .package-card {
            cursor: pointer;
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .package-card.selected {
            border-color: #4e73df;
            background-color: #f8f9fc;
        }
        .package-card:hover {
            border-color: #bac8f3;
        }
        .company-info {
            background-color: #f8f9fc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Assign Package to Company</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item"><a href="{{ route('superadmin.company-packages.index') }}">Company Packages</a></div>
                        <div class="breadcrumb-item active">Assign</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Package Assignment</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('superadmin.company-packages.store') }}" method="POST" id="assignForm">
                                    @csrf

                                    <!-- Company Selection -->
                                    <div class="form-group">
                                        <label for="company_id">Select Company</label>
                                        <select name="company_id" id="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
                                            <option value="">Choose a company...</option>
                                            @foreach($companies ?? [] as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }} ({{ $company->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('company_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Company Info Display -->
                                    <div id="company-info" class="company-info" style="display: none;">
                                        <h5>Company Details</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Name:</strong> <span id="company-name"></span></p>
                                                <p><strong>Email:</strong> <span id="company-email"></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Phone:</strong> <span id="company-phone"></span></p>
                                                <p><strong>Current Package:</strong> <span id="current-package">None</span></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Package Selection -->
                                    <div class="form-group">
                                        <label>Select Package</label>
                                        <div id="packages-grid" class="row">
                                            @foreach($packages ?? [] as $package)
                                                <div class="col-md-4 mb-3">
                                                    <div class="package-card card p-3" data-package-id="{{ $package->id }}">
                                                        <div class="card-body text-center">
                                                            <input type="radio" name="package_id" value="{{ $package->id }}" id="package_{{ $package->id }}" {{ old('package_id') == $package->id ? 'checked' : '' }} required style="display: none;">
                                                            <h5 class="card-title">{{ $package->name }}</h5>
                                                            <p class="card-text">{{ $package->description ?: 'No description' }}</p>
                                                            <div class="price-tag">
                                                                <strong>{{ $package->currency }} {{ number_format($package->base_price, 2) }}</strong>
                                                                @if($package->pricing_type == 'recurring')
                                                                    <small class="text-muted">/{{ $package->billing_cycle }}</small>
                                                                @endif
                                                            </div>
                                                            <div class="mt-2">
                                                                <span class="badge badge-{{ $package->is_active ? 'success' : 'danger' }}">
                                                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </div>
                                                            <div class="mt-2">
                                                                <small class="text-muted">{{ $package->modules->count() }} modules included</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('package_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Assignment Details -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="assigned_at">Assignment Date</label>
                                                <input type="date" name="assigned_at" id="assigned_at" class="form-control @error('assigned_at') is-invalid @enderror" value="{{ old('assigned_at', date('Y-m-d')) }}" required>
                                                @error('assigned_at')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="expires_at">Expiry Date (Optional)</label>
                                                <input type="date" name="expires_at" id="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                                                @error('expires_at')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Options -->
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" name="send_notification" id="send_notification" class="form-check-input" {{ old('send_notification') ? 'checked' : '' }}>
                                            <label for="send_notification" class="form-check-label">
                                                Send notification email to company
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" name="generate_invoice" id="generate_invoice" class="form-check-input" {{ old('generate_invoice') ? 'checked' : '' }}>
                                            <label for="generate_invoice" class="form-check-label">
                                                Generate invoice for this assignment
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Confirmation -->
                                    <div class="form-group">
                                        <div class="alert alert-info">
                                            <strong>Confirmation:</strong> This will assign the selected package to the chosen company. The company will gain access to all modules included in the package.
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Assign Package</button>
                                        <a href="{{ route('superadmin.company-packages.index') }}" class="btn btn-secondary">Cancel</a>
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
            const companySelect = document.getElementById('company_id');
            const companyInfo = document.getElementById('company-info');
            const packageCards = document.querySelectorAll('.package-card');

            // Company selection change
            companySelect.addEventListener('change', function() {
                const companyId = this.value;
                if (companyId) {
                    // Here you could make an AJAX call to fetch company details
                    // For now, we'll just show the info section
                    companyInfo.style.display = 'block';
                    // Update company info (would be populated via AJAX in real implementation)
                } else {
                    companyInfo.style.display = 'none';
                }
            });

            // Package card selection
            packageCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selected class from all cards
                    packageCards.forEach(c => c.classList.remove('selected'));
                    // Add selected class to clicked card
                    this.classList.add('selected');
                    // Check the radio button
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });

            // Pre-select package if editing
            const selectedPackage = document.querySelector('input[name="package_id"]:checked');
            if (selectedPackage) {
                const selectedCard = selectedPackage.closest('.package-card');
                if (selectedCard) {
                    selectedCard.classList.add('selected');
                }
            }

            // Form validation
            document.getElementById('assignForm').addEventListener('submit', function(e) {
                const companySelected = companySelect.value;
                const packageSelected = document.querySelector('input[name="package_id"]:checked');

                if (!companySelected) {
                    e.preventDefault();
                    alert('Please select a company.');
                    return false;
                }

                if (!packageSelected) {
                    e.preventDefault();
                    alert('Please select a package.');
                    return false;
                }
            });
        });
    </script>
@endpush