@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Asset Inventory</h3>
                    <a href="{{ route('assets.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Asset Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Condition</th>
                                    <th>Purchase Cost</th>
                                    <th>Purchase Date</th>
                                    <th>Assigned To</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets as $asset)
                                <tr>
                                    <td>{{ $asset->asset_code }}</td>
                                    <td>{{ $asset->name }}</td>
                                    <td>{{ $asset->category->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $asset->status === 'available' ? 'success' : 'primary' }}">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($asset->condition) }}</td>
                                    <td>₹{{ number_format($asset->purchase_cost, 2) }}</td>
                                    <td>{{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $asset->currentAssignment?->employee->name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No assets found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $assets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection