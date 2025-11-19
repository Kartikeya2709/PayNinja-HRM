@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Asset Assignment Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('assets.assignments.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        @if($assignment->status === 'assigned')
                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#returnAssetModal">
                                <i class="fas fa-undo"></i> Return Asset
                            </button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Left Column: Asset Info -->
                        <div class="col-md-6">
                            <h5>Asset Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Asset Code</th>
                                    <td>{{ $assignment->asset->asset_code }}</td>
                                </tr>
                                <tr>
                                    <th>Asset Name</th>
                                    <td>{{ $assignment->asset->name }}</td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                             
                                    <td>{{ $asset?->category->name ?? $assignment->asset->category->name ?? '-' }}</td>

                                </tr>
                                <tr>
                                    <th>Current Condition</th>
                                    <td>{{ optional($assignment->asset)->condition ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Right Column: Assignment Details -->
                        <div class="col-md-6">
                            <h5>Assignment Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Employee</th>
                                    <td>{{ $assignment->employee->name }}</td>
                                </tr>
                                <tr>
                                    <th>Assigned By</th>
                                    <td>{{ $assignment->assignedBy->name }}</td>
                                </tr>
                                <tr>
                                    <th>Assignment Date</th>
                                    <td>{{ $assignment->assigned_date ? $assignment->assigned_date->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Expected Return</th>
                                    <td>{{ $assignment->expected_return_date ? $assignment->expected_return_date->format('d M, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $assignment->asset->status === 'assigned' ? 'warning' : 'primary' }}">
                                            {{ ucfirst($assignment->asset->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($assignment->returned_date)
                                <tr>
                                    <th>Returned Date</th>
                                    <td>{{ $assignment->returned_date->format('d M, Y') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($assignment->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Notes</h5>
                            <div class="p-3 bg-light rounded">
                                {{ $assignment->notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Return Notes -->
                    @if($assignment->return_notes && $assignment->status === 'returned')
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Return Notes</h5>
                            <div class="p-3 bg-light rounded">
                                {{ $assignment->return_notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Asset Assignment History -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <h5>Assignment History (This Asset)</h5>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Asset</th>
                                        <th>Employee</th>
                                        <th>Assigned By</th>
                                        <th>Assigned Date</th>
                                        <th>Expected Return</th>
                                        <th>Returned Date</th>
                                        <th>Condition on Assignment</th>
                                        <th>Condition on Return</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assetassignmenthistory ?? [] as $history)
                                        <tr @if($history->id === $assignment->id) style="background-color:#eafbe7;" @endif>
                                            <td>{{ $history->id }}</td>
                                            <td>{{ $history->asset->name ?? '-' }}</td>
                                            <td>{{ $history->employee->name ?? '-' }}</td>
                                            <td>{{ $history->assignedBy->name ?? '-' }}</td>
                                            <td>{{ $history->assigned_date ? $history->assigned_date->format('d M, Y') : 'N/A' }}</td>
                                            <td>{{ $history->expected_return_date ? $history->expected_return_date->format('d M, Y') : 'N/A' }}</td>
                                            <td>{{ $history->returned_date ? $history->returned_date->format('d M, Y') : 'Not Returned' }}</td>
                                            <td>{{ ucfirst($history->condition_on_assignment ?? '-') }}</td>
                                            <td>{{ ucfirst($history->condition_on_return ?? '-') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $history->returned_date ? 'success' : 'primary' }}">
                                                    {{ $history->returned_date ? 'Returned' : 'Active' }}
                                                </span>
                                            </td>
                                            <td>{{ $history->notes ?? '-' }}</td>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Return Asset Modal -->
@if($assignment->status === 'assigned')
<div class="modal fade" id="returnAssetModal" tabindex="-1" role="dialog" aria-labelledby="returnAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('assets.assignments.return', $assignment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="returnAssetModalLabel">Return Asset</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="return_condition">Asset Condition on Return <span class="text-danger">*</span></label>
                        <select class="form-control @error('return_condition') is-invalid @enderror" 
                                id="return_condition" name="return_condition" required>
                            <option value="">Select Condition</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="return_notes">Return Notes</label>
                        <textarea class="form-control" id="return_notes" name="return_notes" rows="3" 
                                  placeholder="Enter any notes about the asset's return or its condition"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Return Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
