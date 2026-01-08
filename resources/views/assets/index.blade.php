@extends('layouts.app')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Assets</h3>
                    <div class="btn-group">
                        {{-- @if(url()->previous() == route('assets.dashboard'))
                        <a href="{{ route('assets.dashboard') }}" class="btn btn-warning">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        @endif --}}
                        @if(\App\Models\User::hasAccess('assets/create',true))
                            <a href="{{ route('assets.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Asset
                            </a>
                        @endif
                    </div>
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
                                    <th>Date Assigned</th>
                                    <th>Current Assignment</th>
                                    <th>Condition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets as $asset)
                                <tr>
                                    <td>{{ $asset->asset_code }}</td>
                                    <td>{{ $asset->name }}</td>
                                    <td>{{ $asset->category->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $asset->status === 'available' ? 'success' : ($asset->status === 'assigned' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>

                                    <td>{{ $asset->currentAssignment?->assigned_date?->format('Y-m-d') ?? '-' }}</td>

                                    <td>
                                        @if($asset->currentAssignment)
                                            {{ $asset->currentAssignment->employee->name ?? 'N/A'}}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                     {{ $asset->condition }}
                                        {{-- {{ optional($asset->currentAssignment)->condition_on_assignment ?? '-' }}    --}}
                                    </td>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                        <!-- View Asset -->
                                        @if(\App\Models\User::hasAccess('assets/show/{encryptedId}',true))
                                        <a href="{{ route('assets.show', ['encryptedId' => Crypt::encrypt($asset->id)]) }}"
                                        class="btn btn-outline-info btn-sm action-btn"
                                        data-id="{{ $asset->id }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="View Asset" aria-label="View">
                                        <span class="btn-content">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </a>
                                        @endif

                                        <!-- Edit Asset -->
                                        @if(\App\Models\User::hasAccess('assets/{encryptedId}/edit',true))
                                        <a href="{{ route('assets.edit', ['encryptedId' => Crypt::encrypt($asset->id)]) }}"
                                        class="btn btn-outline-primary btn-sm action-btn"
                                        data-id="{{ $asset->id }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Edit Asset" aria-label="Edit">
                                        <span class="btn-content">
                                            <i class="fas fa-edit"></i>
                                        </span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </a>
                                        @endif

                                        <!-- Assign Asset -->
                                        @if($asset->status === 'available')
                                          @if(\App\Models\User::hasAccess('assets/create',true))
                                        <a href="{{ route('assets.assignments.create') }}"
                                        class="btn btn-outline-success btn-sm action-btn"
                                        data-id="{{ $asset->id }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Assign Asset" aria-label="Assign">
                                        <span class="btn-content">
                                        <i class="fas fa-user-plus"></i>
                                        </span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </a>
                                        @endif
                                        @endif
                                        <!-- Delete Asset -->
                                        @if(\App\Models\User::hasAccess('assets/{encryptedId}/delete',true))
                                        <form action="{{ route('assets.destroy', ['encryptedId' => Crypt::encrypt($asset->id)]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this asset?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Asset">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                        </div>
                                    </td>
                                    {{-- {{-- <td>
                                        <!-- View Asset -->
                                        <a href="{{ route('assets.show', ['encryptedId' => Crypt::encrypt($asset->id)]) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Edit Asset -->
                                        <a href="{{ route('assets.edit', ['encryptedId' => Crypt::encrypt($asset->id)]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Assign Asset (only if available) -->
                                        @if($asset->status === 'available')
                                            <a href="{{ route('assets.assignments.create') }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                        @endif
                                    </td> --}}

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No assets found.</td>
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
