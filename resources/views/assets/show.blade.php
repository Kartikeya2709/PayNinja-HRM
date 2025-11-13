@extends('layouts.app')

@section('title', 'Asset Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-1">
                    <h5 class="mb-0">Asset Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.assets.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
                <div class="card-body card mt-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="asset-card rounded-4 p-4">
                                <div class="asset-info-card">
                                <div class="accent-bar mb-3"></div>
                                <h5 class="fw-bold text-dark mb-4">
                                <i class="fas fa-cube me-2 text-primary"></i>Asset Details
                                </h5>

                                <div class="info-grid">
                                <div><span>Name:</span> {{ $asset->name }}</div>
                                <div><span>Category:</span> {{ $asset->category->name }}</div>
                                <div><span>Asset Code:</span> {{ $asset->asset_code }}</div>
                                <div>
                                <span>Status:</span> 
                                <span class="badge badge-{{ $asset->status === 'available' ? 'success' : 'warning' }}">
                                {{ ucfirst($asset->status) }}
                                </span>
                                </div>
                                <div><span>Description:</span> {{ $asset->description ?? '-' }}</div>
                                <div><span>Purchase Date:</span> {{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-' }}</div>
                                <div>
                                <span>Current Assignment:</span>
                                @if($asset->assignments && $asset->assignments->last() && $asset->assignments->last()->returned_date === null)
                                {{ $asset->assignments->last()->employee->name }}
                                @else
                                N/A
                                @endif
                                </div>
                                <div>
                                <span>Condition:</span>
                                @if($asset->currentAssignment)
                                {{ $asset->currentAssignment->condition_on_assignment ?? $asset->currentAssignment->condition_on_return }}
                                @else
                               {{ $asset->condition ?? 'N/A' }}
                               @endif
                               </div>
                               </div>
                               </div>
                            </div>

                        </div>
                    </div>
                </div>
                    <!-- Asset Assignment History -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mt-4">
                            <h5 class="text-center mb-3">Assignment History</h5>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>S.no</th>
                                        <th>Employee</th>
                                        <th>Assigned By</th>
                                        <th>Assigned Date</th>
                                        <th>Expected Return</th>
                                        <th>Returned Date</th>
                                        <th>Condition on Assignment</th>
                                        <th>Condition on Return</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        {{-- <th>Actions</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($asset->assignments as $assignment)
                                        <tr @if($assignment->status === 'assigned') style="background-color:#eafbe7;" @endif>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $assignment->employee->name ?? '-' }}</td>
                                            <td>{{ $assignment->assignedBy->name ?? '-' }}</td>
                                            <td>{{ $assignment->assigned_date ? \Carbon\Carbon::parse($assignment->assigned_date)->format('d M, Y') : 'N/A' }}</td>
                                            <td>{{ $assignment->expected_return_date ? \Carbon\Carbon::parse($assignment->expected_return_date)->format('d M, Y') : 'N/A' }}</td>
                                            <td>{{ $assignment->returned_date ? \Carbon\Carbon::parse($assignment->returned_date)->format('d M, Y') : 'Not Returned' }}</td>
                                            <td>{{ ucfirst($assignment->condition_on_assignment ?? '-') }}</td>
                                            <td>{{ ucfirst($assignment->condition_on_return ?? '-') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $assignment->returned_date ? 'success' : 'primary' }}">
                                                    {{ $assignment->returned_date ? 'Returned' : 'Active' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($assignment->notes)
                                                      {{ $assignment->notes }}
                                                        {{-- <i class="fas fa-info-circle"></i> --}}
                                          
                                                @endif
                                                @if($assignment->return_notes)
                                                    {{ $assignment->return_notes }}
                                                @endif
                                            </td>
                                            {{-- <td>
                                                <a href="{{ route('admin.assets.assignments.show', $assignment->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($assignment->status === 'assigned')
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="window.location.href='{{ route('admin.assets.assignments.show', $assignment->id) }}#returnAssetModal'">
                                                        <i class="fas fa-undo"></i> Return
                                                    </button>
                                                @endif
                                            </td> --}}
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">No assignment history found for this asset.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End Asset History -->

                    @if($asset->conditions->count() > 0)
                    <div class="mt-4">
                        <h4>Condition History</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Condition</th>
                                    <th>Notes</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asset->conditions as $condition)
                                <tr>
                                    <td>{{ $condition->condition }}</td>
                                    <td>{{ $condition->notes }}</td>
                                    <td>{{ $condition->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection