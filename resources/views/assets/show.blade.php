@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Asset Details: {{ $asset->name }}</h3>
                    <div>
                       <a href="{{ route('admin.assets.edit', $asset->id) }}" class="btn btn-primary">
                           <i class="fas fa-edit"></i> Edit Asset
                       </a>
                        <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Assets
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table">
                                <tr>
                                    <th>Asset Code:</th>
                                    <td>{{ $asset->asset_code }}</td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $asset->name }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $assignment->asset->category->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge badge-{{ $asset->status === 'available' ? 'success' : ($asset->status === 'assigned' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($asset->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Current Condition:</th>
                                    <td>{{ $asset->condition_on_assignment }}</td>
                                </tr>
                                <tr>
                                  @if($assignment->returned_date)
                                        <td>{{ $assignment->condition_on_return }}</td>
                                    @else
                                    <td>{{ $assignment->condition_on_assignment }}</td>
                                    @endif
                                    <td>{{ $assignment->returned_date ? 'Returned' : 'In Use' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Purchase Information</h5>
                            <table class="table">
                                <tr>
                                    <th>Purchase Date:</th>
                                    <td>{{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Purchase Cost:</th>
                                    <td>{{ $asset->purchase_cost ? number_format($asset->purchase_cost, 2) : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $asset->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $asset->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Description</h5>
                            <p>{{ $asset->description ?? 'No description available.' }}</p>
                        </div>
                    </div>

                    @if($asset->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Notes</h5>
                            <p>{{ $asset->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Assignment History</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Assigned Date</th>
                                            <th>Expected Return</th>
                                            <th>Returned Date</th>
                                            <th>Assigned By</th>
                                            <th>Condition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($asset->assignments as $assignment)
                                        <tr>
                                            <td>{{ $assignment->employee->full_name }}</td>
                                            <td>{{ $assignment->assigned_date->format('Y-m-d') }}</td>
                                            <td>{{ $assignment->expected_return_date ? $assignment->expected_return_date->format('Y-m-d') : '-' }}</td>
                                            <td>{{ $assignment->returned_date ? $assignment->returned_date->format('Y-m-d') : 'Not returned' }}</td>
                                            <td>{{ $assignment->assignedBy->name }}</td>
                                            <td>{{ $assignment->condition_on_assignment }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No assignment history found.</td>
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