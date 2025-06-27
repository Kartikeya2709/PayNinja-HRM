@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Attendance Regularization Requests</div>

                <div class="card-body">
                    @if (!is_null(Auth::user()->employee->reporting_manager_id))
                        <a href="{{ route('regularization.requests.create') }}" class="btn btn-primary mb-3">New Request</a>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(is_null(Auth::user()->employee->reporting_manager_id))
                    <form action="{{ route('regularization.requests.bulk-update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <button type="submit" name="action" value="approve" class="btn btn-success">Approve Selected</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">Reject Selected</button>
                        </div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                @if(is_null(Auth::user()->employee->reporting_manager_id))
                                <th><input type="checkbox" id="select-all"></th>
                                @endif
                                <th>Date</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Applied By</th>
                                <th>Approved By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr>
                                    @if(is_null(Auth::user()->employee->reporting_manager_id))
                                    <td><input type="checkbox" name="request_ids[]" value="{{ $request->id }}"></td>
                                    @endif
                                    <td>{{ $request->date }}</td>
                                    <td>{{ $request->reason }}</td>
                                    <td>{{ ucfirst($request->status) }}</td>
                                    <td>{{ $request->employee->name }}</td>
                                    <td>{{ $request->approver->name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('regularization.requests.show', $request->id) }}" class="btn btn-sm btn-info">View</a>
                                        @if ($request->status == 'pending' && Auth::user()->employee->id != $request->employee_id)
                                            <a href="{{ route('regularization.requests.edit', $request->id) }}" class="btn btn-sm btn-warning">Review</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if(is_null(Auth::user()->employee->reporting_manager_id))
                    </form>
                    @endif

                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(is_null(Auth::user()->employee->reporting_manager_id))
<script>
    document.getElementById('select-all').addEventListener('click', function(event) {
        let checkboxes = document.querySelectorAll('input[name="request_ids[]"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = event.target.checked;
        });
    });
</script>
@endif
@endpush
