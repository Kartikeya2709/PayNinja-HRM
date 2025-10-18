@extends('layouts.app')

@section('title', 'Demo Requests')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Demo Requests</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Company Name</th>
                                    <th>Company Size</th>
                                    <th>Request Date</th>
                                    <th>Additional Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($demoRequests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>{{ $request->name }}</td>
                                        <td>{{ $request->email }}</td>
                                        <td>{{ $request->phone ?: 'N/A' }}</td>
                                        <td>{{ $request->company_name ?: 'N/A' }}</td>
                                        <td>{{ $request->company_size ?: 'N/A' }}</td>
                                        <td>{{ $request->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <div class="truncate-text" title="{{ $request->additional_info }}">
                                                {{ Str::limit($request->additional_info, 50) }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No demo requests found.</td>
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

<style>
.truncate-text {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
@endsection
