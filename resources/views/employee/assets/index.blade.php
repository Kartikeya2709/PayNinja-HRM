@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">My Assets History</h3>
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
                                    <th>Asset Name</th>
                                    <th>Category</th>
                                    <th>Assignment Date</th>
                                    <th>Expected Return Date</th>
                                    <th>Return Date</th>
                                    <th>Condition</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->asset->asset_code }}</td>
                                    <td>{{ $assignment->asset->name }}</td>
                                    <td>{{ $assignment->asset->category->name }}</td>
                                    <td>{{ $assignment->assigned_date?->format('Y-m-d') ?? '-' }}</td>
                                    <td>{{ $assignment->expected_return_date?->format('Y-m-d') ?? '-' }}</td>
                                    <td>{{ $assignment->returned_date?->format('Y-m-d') ?? '-' }}</td>
                                    <td>{{ $assignment->condition_on_assignment ?? $assignment->asset->condition }}</td>
                                    <td>
                                        @if($assignment->returned_date)
                                            <span class="badge badge-success">Returned</span>
                                        @else
                                            <span class="badge badge-primary">Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No assets assigned to you.</td>
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
@endsection