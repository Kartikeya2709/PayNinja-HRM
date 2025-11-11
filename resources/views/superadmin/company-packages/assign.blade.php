@extends('layouts.app')

@section('title', 'Assign Package to Company')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .form-group label { font-weight: bold; }
        .card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
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
                        <div class="breadcrumb-item active">Assign Package</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Package Assignment Form</h4>
                            </div>
                            <div class="card-body">
                                <form id="assign-form" method="POST" action="{{ route('superadmin.company-packages.assign') }}">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="company_id">Select Company</label>
                                                <select name="company_id" id="company_id" class="form-control" required>
                                                    <option value="">Choose a company...</option>
                                                    @foreach($companies as $company)
                                                        <option value="{{ $company->id }}">{{ $company->name }} ({{ $company->email }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="package_id">Select Package</label>
                                                <select name="package_id" id="package_id" class="form-control" required>
                                                    <option value="">Choose a package...</option>
                                                    @foreach($packages as $package)
                                                        <option value="{{ $package->id }}" data-price="{{ $package->base_price }}" data-currency="{{ $package->currency }}">
                                                            {{ $package->name }} - {{ $package->currency }} {{ number_format($package->base_price, 2) }} ({{ ucfirst($package->pricing_type) }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="assigned_at">Assignment Date</label>
                                                <input type="date" name="assigned_at" id="assigned_at" class="form-control" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="expires_at">Expiry Date (Optional)</label>
                                                <input type="date" name="expires_at" id="expires_at" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="send_notification" id="send_notification" class="form-check-input" value="1">
                                                <label class="form-check-label" for="send_notification">
                                                    Send notification to company
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="generate_invoice" id="generate_invoice" class="form-check-input" value="1" checked>
                                                <label class="form-check-label" for="generate_invoice">
                                                    Generate invoice automatically
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Assign Package
                                        </button>
                                        <a href="{{ route('superadmin.company-packages.index') }}" class="btn btn-secondary ml-2">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
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
        $(document).ready(function() {
            // Form validation
            $('#assign-form').on('submit', function(e) {
                const companyId = $('#company_id').val();
                const packageId = $('#package_id').val();

                if (!companyId || !packageId) {
                    e.preventDefault();
                    alert('Please select both a company and a package.');
                    return false;
                }

                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');
            });

            // Auto-set expiry date based on package if needed
            $('#package_id').on('change', function() {
                // You can add logic here to set default expiry based on package type
            });
        });
    </script>
@endpush