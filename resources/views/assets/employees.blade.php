@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Employees with Assigned Assets</h3>
                    <a href="{{ route('assets.dashboard') }}" class="btn btn-warning">
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
                                    <th>Employee Code</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Assets Count</th>
                                    <th>Assigned Assets</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                <tr>
                                    <td>{{ $employee->employee_code }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                    <td>{{ $employee->designation->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $employee->assignments->whereNull('returned_date')->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($employee->assignments->whereNull('returned_date')->count() > 0)
                                            <ul class="list-unstyled mb-0">
                                                @foreach($employee->assignments->whereNull('returned_date') as $assignment)
                                                <li>
                                                    <strong>{{ $assignment->asset->name }}</strong>
                                                    ({{ $assignment->asset->asset_code }})
                                                    <br>
                                                    <small class="text-muted">
                                                        Assigned: {{ $assignment->assigned_date->format('M d, Y') }}
                                                        @if($assignment->expected_return_date)
                                                            | Expected Return: {{ $assignment->expected_return_date->format('M d, Y') }}
                                                        @endif
                                                    </small>
                                                </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">No assets assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No employees with assigned assets found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection