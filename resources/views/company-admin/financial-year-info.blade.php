@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Current Financial Year Information</h3>
                </div>

                <div class="card-body">
                    @if($financialYear)
                    <div class="alert alert-success">
                        <h4>Active Financial Year</h4>
                        <p><strong>Name:</strong> {{ $financialYear->name }}</p>
                        <p><strong>Period:</strong> {{ $financialYear->start_date->format('M d, Y') }} - {{ $financialYear->end_date->format('M d, Y') }}</p>
                        <p><strong>Status:</strong>
                            @if($financialYear->is_active)
                            <span class="badge badge-success">Active</span>
                            @else
                            <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </p>
                        <p><strong>Locked:</strong>
                            @if($financialYear->is_locked)
                            <span class="badge badge-danger">Locked</span>
                            @else
                            <span class="badge badge-info">Unlocked</span>
                            @endif
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5>Financial Year Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Duration:</strong> {{ $financialYear->start_date->diffInDays($financialYear->end_date) + 1 }} days</p>
                                    <p><strong>Months:</strong> {{ $financialYear->start_date->diffInMonths($financialYear->end_date) + 1 }} months</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Created:</strong> {{ $financialYear->created_at->format('M d, Y H:i:s') }}</p>
                                    <p><strong>Updated:</strong> {{ $financialYear->updated_at->format('M d, Y H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('company-admin.financial-years.index') }}" class="btn btn-primary">
                            <i class="fas fa-calendar-alt"></i> Manage Financial Years
                        </a>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <h4>No Active Financial Year</h4>
                        <p>No financial year has been set for this company. Please set up a financial year to enable disbursement cycles.</p>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('company-admin.financial-years.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Financial Year
                        </a>
                    </div>
                    @endif
                </div>

                <div class="card-footer bg-light">
                    <p class="text-muted mb-0">
                        <strong>Note:</strong> The financial year determines the period for leave disbursement cycles and financial calculations.
                        Only one financial year can be active at a time.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
