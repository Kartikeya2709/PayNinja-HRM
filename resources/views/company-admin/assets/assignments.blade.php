@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Recent Asset Assignments</h3>
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
                                    <th>Asset</th>
                                    <th>Asset Code</th>
                                    <th>Employee</th>
                                    <th>Employee Code</th>
                                    <th>Assigned Date</th>
                                    <th>Expected Return</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                    <th>Condition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->asset->name }}</td>
                                    <td>{{ $assignment->asset->asset_code }}</td>
                                    <td>{{ $assignment->employee->name ?? 'N/A' }}</td>
                                    <td>{{ $assignment->employee->employee_code ?? 'N/A' }}</td>
                                    <td>{{ $assignment->assigned_date->format('Y-m-d') }}</td>
                                    <td>{{ $assignment->expected_return_date ? $assignment->expected_return_date->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $assignment->returned_date ? $assignment->returned_date->format('Y-m-d') : '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $assignment->returned_date ? 'success' : 'primary' }}">
                                            {{ $assignment->returned_date ? 'Returned' : 'Active' }}
                                        </span>
                                    </td>
                                    <td>{{ $assignment->returned_date ? $assignment->condition_on_return : $assignment->condition_on_assignment }}</td>
                                    <td>
                                        <a href="{{ route('assets.assignments.show', $assignment->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No asset assignments found.</td>
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