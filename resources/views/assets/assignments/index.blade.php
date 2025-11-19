@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Asset Assignments</h3>
                    <a href="{{ route('assets.assignments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Assignment
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
                                    <th>Asset</th>
                                    <th>Employee</th>
                                    <th>Assigned Date</th>
                                    <th>Expected Return</th>
                                    <th>Status</th>
                                    <th>Condition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->asset->name }} ({{ $assignment->asset->asset_code }})</td>
                                    <td>{{ $assignment->employee->name }}</td>
                                    <td>{{ $assignment->assigned_date->format('Y-m-d') }}</td>
                                    <td>{{ $assignment->expected_return_date ? $assignment->expected_return_date->format('Y-m-d') : '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $assignment->returned_date ? 'success' : 'primary' }}">
                                            {{ $assignment->returned_date ? 'Returned' : 'Active' }}
                                        </span>
                                    </td>
                                    @if($assignment->returned_date)
                                        <td>{{ $assignment->condition_on_return }}</td>
                                    @else
                                        <td>{{ $assignment->condition_on_assignment }}</td>
                                    @endif
                                    <td>
                                        <a href="{{ route('assets.assignments.show', $assignment->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Return Button -->
                                        @if(!$assignment->returned_date)
                                            <button type="button" 
                                                    class="btn btn-warning btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#returnModal{{ $assignment->id }}">
                                                <i class="fas fa-undo"></i> Return
                                            </button>
                                        @endif
                                        <!-- Modal -->
                                        <div class="modal fade" id="returnModal{{ $assignment->id }}" tabindex="-1" aria-labelledby="returnModalLabel{{ $assignment->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('assets.assignments.return', $assignment->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="returnModalLabel{{ $assignment->id }}">Return Asset</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="returned_date_{{ $assignment->id }}">Return Date</label>
                                                                <input type="date" name="returned_date" id="returned_date_{{ $assignment->id }}" class="form-control" value="{{ now()->format('Y-m-d') }}">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="return_condition_{{ $assignment->id }}">Condition on Return</label>
                                                                <select name="return_condition" id="return_condition_{{ $assignment->id }}" class="form-control" required>
                                                                    <option value="">-- Select Condition --</option>
                                                                    <option value="good">Good</option>
                                                                    <option value="fair">Fair</option>
                                                                    <option value="poor">Poor</option>
                                                                    <option value="damaged">Damaged</option>
                                                                </select>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="return_notes_{{ $assignment->id }}">Notes</label>
                                                                <textarea name="return_notes" id="return_notes_{{ $assignment->id }}" class="form-control" placeholder="Optional notes..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Confirm Return</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No asset assignments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $assignments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<!-- in layouts.app -->

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

@endpush