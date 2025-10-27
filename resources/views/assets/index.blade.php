@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Assets</h3>
                    <a href="{{ route('admin.assets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Asset
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
                                    <th>Code</th>
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
                                            {{ $asset->currentAssignment->employee->name }}
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
                                        <a href="{{ route('admin.assets.show', $asset->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.assets.edit', $asset->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($asset->status === 'available')
                                            <a href="{{ route('admin.assets.assignments.create', ['asset' => $asset->id]) }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                        @endif
                                    </td>
                                    {{-- <td>
                                        <!-- View Asset -->
                                        <a href="{{ route('admin.assets.show', $asset->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Edit Asset -->
                                        <a href="{{ route('admin.assets.assignments.edit', $asset->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Assign Asset (only if available) -->
                                        @if($asset->status === 'available')
                                            <a href="{{ route('admin.assets.assignments.create', ['asset' => $asset->id]) }}" class="btn btn-success btn-sm">
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