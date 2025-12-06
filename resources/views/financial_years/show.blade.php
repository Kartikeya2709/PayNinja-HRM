@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Financial Year Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('company-admin.financial-years.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Financial Year Name</label>
                                <p class="form-control-static">{{ $financialYear->name }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <p class="form-control-static">
                                    @if($financialYear->is_active)
                                    <span class="badge badge-success">Active</span>
                                    @else
                                    <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date</label>
                                <p class="form-control-static">
                                    {{ $financialYear->start_date->format('M d, Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>End Date</label>
                                <p class="form-control-static">
                                    {{ $financialYear->end_date->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Locked Status</label>
                                <p class="form-control-static">
                                    @if($financialYear->is_locked)
                                    <span class="badge badge-danger">Locked</span>
                                    @else
                                    <span class="badge badge-info">Unlocked</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Created At</label>
                                <p class="form-control-static">
                                    {{ $financialYear->created_at->format('M d, Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Period Duration</label>
                                <p class="form-control-static">
                                    {{ $financialYear->start_date->diffInDays($financialYear->end_date) + 1 }} days
                                    ({{ $financialYear->start_date->diffInMonths($financialYear->end_date) }} months)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="btn-group">
                        @if(!$financialYear->is_locked)
                        <a href="{{ route('company-admin.financial-years.edit', $financialYear->id) }}"
                           class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endif

                        @if($financialYear->is_locked)
                        <form action="{{ route('company-admin.financial-years.unlock', $financialYear->id) }}"
                              method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary"
                                    onclick="return confirm('Are you sure you want to unlock this financial year?')">
                                <i class="fas fa-unlock"></i> Unlock
                            </button>
                        </form>
                        @else
                        <form action="{{ route('company-admin.financial-years.lock', $financialYear->id) }}"
                              method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to lock this financial year? This cannot be undone.')">
                                <i class="fas fa-lock"></i> Lock
                            </button>
                        </form>
                        @endif

                        @if(!$financialYear->is_active)
                        <form action="{{ route('company-admin.financial-years.activate', $financialYear->id) }}"
                              method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success"
                                    onclick="return confirm('Are you sure you want to activate this financial year?')">
                                <i class="fas fa-check"></i> Activate
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
