@extends('layouts.app')

@section('title', 'Beneficiary Badges Management')

@section('content')
<div class="container-fluid">
    <div class="row beneficiary-badges">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Beneficiary Badges</h4>
                    <p class="mb-0">Manage company-wide allowances and deductions for payroll.</p>
                </div>
                <a href="{{ route('admin.payroll.beneficiary-badges.create') }}" class="btn btn-primary add-list"><i class="fa-solid fa-plus me-2"></i>Create New Badge</a>
            </div>
        </div>

        @if(session('success'))
            <div class="col-lg-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="col-lg-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">All Beneficiary Badges</h5>
                </div>
                <div class="card-body">
                    @if($beneficiaryBadges->isEmpty())
                        <div class="text-center">
                            <p>No beneficiary badges found. <a href="{{ route('admin.payroll.beneficiary-badges.create') }}">Create one now!</a></p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Calculation</th>
                                        <th>Value</th>
                                        <th>Based On</th>
                                        <th>Status</th>
                                        <th>Company-wide</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($beneficiaryBadges as $badge)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.payroll.beneficiary-badges.show', $badge->id) }}" class="text-primary">
                                                    {{ $badge->name }}
                                                </a>
                                            </td>
                                            <td><span class="badge bg-{{ $badge->type == 'allowance' ? 'success' : 'danger' }}">{{ ucfirst($badge->type) }}</span></td>
                                            <td>{{ ucfirst($badge->calculation_type) }}</td>
                                            <td>
                                                @if($badge->calculation_type == 'flat')
                                                    {{-- Assuming currency formatting is handled elsewhere or use a helper --}}
                                                    {{ number_format($badge->value, 2) }}
                                                @else
                                                    {{ $badge->value }}%
                                                @endif
                                            </td>
                                            <td>{{ $badge->based_on ?? 'N/A' }}</td>
                                            <td>
                                                @if($badge->is_active)
                                                    <span class="badge bg-primary">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($badge->is_company_wide)
                                                    <span class="badge bg-info">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{ route('admin.payroll.beneficiary-badges.show', $badge->id) }}" class="btn btn-sm btn-soft-info me-1" title="View Details">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.payroll.beneficiary-badges.edit', $badge->id) }}" class="btn btn-sm btn-soft-primary me-1" title="Edit">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <form action="{{ route('admin.payroll.beneficiary-badges.destroy', $badge->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this badge? This action cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $beneficiaryBadges->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
