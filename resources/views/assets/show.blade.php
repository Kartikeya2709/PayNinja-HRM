@extends('layouts.app')

@section('title', 'Asset Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Asset Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.assets.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $asset->name }}</td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>{{ $asset->category->name }}</td>
                                </tr>
                                <tr>
                                    <th>Asset Code</th>
                                    <td>{{ $asset->asset_code }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $asset->status === 'available' ? 'success' : 'warning' }}">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $asset->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Purchase Date</th>
                                    <td>{{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Current Assignment</th>
                                    <td>
                                        @if($asset->assignments && $asset->assignments->last()->returned_date === null)
                                            {{ $asset->assignments->last()->employee->name }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Condition</th>
                                    <td>
                                        @if($asset->currentAssignment)
                                            {{ $asset->currentAssignment->condition_on_assignment ??$asset->currentAssignment->condition_on_return }}
                                        @else
                                            {{ $asset->condition ?? 'N/A' }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Asset Assignment History -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <h5>Assignment History</h5>
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