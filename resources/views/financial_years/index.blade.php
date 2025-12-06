@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Financial Years Management</h3>
                    <div class="card-tools">
                        @if(!$financialYears->contains('is_active', true))
                        <a href="{{ route('company-admin.financial-years.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Financial Year
                        </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Locked</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($financialYears as $financialYear)
                                <tr>
                                    <td>{{ $financialYear->name }}</td>
                                    <td>
                                        {{ $financialYear->start_date->format('M d, Y') }} -
                                        {{ $financialYear->end_date->format('M d, Y') }}
                                    </td>
                                    <td>
                                        @if($financialYear->is_active)
                                        <span class="badge badge-success">Active</span>
                                        @else
                                        <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($financialYear->is_locked)
                                        <span class="badge badge-danger">Locked</span>
                                        @else
                                        <span class="badge badge-info">Unlocked</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('company-admin.financial-years.show', $financialYear->id) }}"
                                               class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if(!$financialYear->is_locked)
                                            <a href="{{ route('company-admin.financial-years.edit', $financialYear->id) }}"
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            @if(!$financialYear->is_active)
                                            <form action="{{ route('company-admin.financial-years.activate', $financialYear->id) }}"
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" title="Activate"
                                                        onclick="return confirm('Are you sure you want to activate this financial year?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            @endif

                                            @if($financialYear->is_locked)
                                            <form action="{{ route('company-admin.financial-years.unlock', $financialYear->id) }}"
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm" title="Unlock"
                                                        onclick="return confirm('Are you sure you want to unlock this financial year?')">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            </form>
                                            @else
                                            <form action="{{ route('company-admin.financial-years.lock', $financialYear->id) }}"
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm" title="Lock"
                                                        onclick="return confirm('Are you sure you want to lock this financial year? This cannot be undone.')">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            </form>
                                            @endif

                                            @if(!$financialYear->is_active)
                                            <form action="{{ route('company-admin.financial-years.destroy', $financialYear->id) }}"
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this financial year?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No financial years found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <p class="text-muted">
                        <strong>Note:</strong> Only one financial year can be active at a time. The active financial year
                        will be used for all disbursement cycles and financial calculations.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
