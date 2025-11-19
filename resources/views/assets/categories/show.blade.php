@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-1">
                    <h5 class="mb-0">Category</h5>
                        <div class="card-tools">
                           <a href="{{ route('assets.categories.index') }}" class="btn btn-secondary">
                           <i class="fas fa-arrow-left"></i> Back to Categories
                           </a>
                        </div>
                </div>
            </div>
                <div class="card-body">
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="category-card card">
                                <div class="category-info-card">
                                <div class="accent-bar mb-3"></div>
                                    <h5 class="fw-bold text-dark mb-4"><i class="fas fa-folder-open me-2 text-primary"></i>Category Details</h5>

                                    <div class="info-grid">
                                        <div><span>Name:</span>{{ $category->name }}</div>
                                        <div><span>Description:</span> {{ $category->description ?? 'N/A' }}</div>
                                        <div><span>Total Assets:</span> {{ $category->assets->count() }}</div>
                                        <div><span>Created At:</span> {{ $category->created_at->format('Y-m-d H:i:s') }}</div>
                                        <div><span>Last Updated:</span> {{ $category->updated_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4">
                            <h5 class="text-center mb-3">Assets in this Category</h5>
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
                                                <div class="btn-group btn-group-sm">

                                                <!-- View Asset Button -->
                                                <a href="{{ route('assets.show', $asset->id) }}"
                                                class="btn btn-outline-info btn-sm action-btn"
                                                data-id="{{ $asset->id }}"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="View Asset"
                                                aria-label="View">
                                                <span class="btn-content">
                                                <i class="fas fa-eye"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                </a>

                                                <!-- Edit Asset Button -->
                                                <a href="{{ route('assets.edit', $asset->id) }}"
                                                class="btn btn-outline-primary btn-sm action-btn"
                                                data-id="{{ $asset->id }}"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Edit Asset"
                                                aria-label="Edit">
                                                <span class="btn-content">
                                                <i class="fas fa-edit"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                </a>
                                               </div>

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
</div>
@endsection