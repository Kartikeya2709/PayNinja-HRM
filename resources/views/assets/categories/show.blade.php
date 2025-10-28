@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Category: {{ $category->name }}</h3>
                    <a href="{{ route('admin.assets.categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Categories
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Category Details</h5>
                            <table class="table">
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $category->name }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $category->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Total Assets:</th>
                                    <td>{{ $category->assets->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $category->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $category->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h5>Assets in this Category</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Asset Code</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Condition</th>
                                            <th>Current Assignment</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($category->assets as $asset)
                                        <tr>
                                            <td>{{ $asset->asset_code }}</td>
                                            <td>{{ $asset->name }}</td>
                                            <td>
                                                <span class="badge badge-{{ $asset->status === 'available' ? 'success' : ($asset->status === 'assigned' ? 'primary' : 'warning') }}">
                                                    {{ ucfirst($asset->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $asset->condition }}</td>
                                            <td>
                                                @if($asset->currentAssignment)
                                                    {{ $asset->currentAssignment->employee->full_name }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.assets.show', $asset->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.assets.edit', $asset->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No assets found in this category.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection