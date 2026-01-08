@extends('layouts.app')

@section('title', 'My Regularization Requests')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>My Regularization Requests</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">My Regularization Requests</a></div>
            </div>
        </div>
        <section class="card">
            <div class="card-1">
                <h5 class="mb-0">My Regularization Requests</h5>
                <div class="section-header-button">
                    @if(\App\Models\User::hasAccess('attendance-management/regularization-request-create', true))
                    <a href="{{ route('regularization-requests.create') }}" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Create New Request">
                        <i class="fas fa-plus"></i> Create Request
                    </a>
                    @endif

                    @if(\App\Models\User::hasAccess('attendance-management/regularization-requests/my/show', true))
                    <button type="button" class="btn btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Show My Requests" onclick="showMyRequests()">
                        <i class="fas fa-eye"></i> Show My Requests
                    </button>
                    @endif

                    {{-- @if(\App\Models\User::hasAccess('attendance-management/regularization-requests/my/delete', true))
                    <button type="button" class="btn btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete My Requests" onclick="deleteMyRequests()">
                        <i class="fas fa-trash"></i> Delete My Requests
                    </button>
                    @endif --}}
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12 px-0">
                        <div class="card">
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible show fade">
                                        <div class="alert-body">
                                            <button class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                            {{ session('success') }}
                                        </div>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible show fade">
                                        <div class="alert-body">
                                            <button class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                            {{ session('error') }}
                                        </div>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Date</th>
                                                <th>Clock In</th>
                                                <th>Clock Out</th>
                                                <th>Reason</th>
                                                <th>Status</th>
                                                <th>Approver</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($requests->count() > 0)
                                                @foreach($requests as $key => $request)
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        <td>{{ $request->date }}</td>
                                                        <td>{{ $request->check_in ?? 'N/A' }}</td>
                                                        <td>{{ $request->check_out ?? 'N/A' }}</td>
                                                        <td>{{ $request->reason }}</td>
                                                        <td>
                                                            <span class="badge @if($request->status == 'pending') bg-warning @elseif($request->status == 'approved') bg-success @else bg-danger @endif">
                                                                {{ ucfirst($request->status) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $request->approver->name ?? '' }}</td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                @if(\App\Models\User::hasAccess('attendance-management/regularization-request-show/{encryptedId}', true))
                                                                    <a href="{{ route('regularization-requests.show', Crypt::encrypt($request->id)) }}"
                                                                       class="btn btn-info"
                                                                       data-bs-toggle="tooltip"
                                                                       data-bs-placement="top"
                                                                       title="View Request">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                @endif



                                                                @if(\App\Models\User::hasAccess('attendance-management/regularization-request-delete/{encryptedId}', true) && $request->status == 'pending')
                                                                    <form action="{{ route('regularization-requests.destroy', Crypt::encrypt($request->id)) }}"
                                                                          method="POST"
                                                                          style="display: inline-block;"
                                                                          onsubmit="return confirm('Are you sure you want to delete this regularization request?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                                class="btn btn-danger"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-placement="top"
                                                                                title="Delete Request">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="8" class="text-center">
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i>
                                                            No regularization requests found.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                @if($requests instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                    {{ $requests->links() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</div>

<script>
function showMyRequests() {
    // Refresh the page to show all requests
    window.location.href = '{{ route("regularization-requests.my") }}';
}

function deleteMyRequests() {
    if (confirm('Are you sure you want to delete all your pending regularization requests? This action cannot be undone.')) {
        // Get all pending request IDs
        const pendingRequests = [];
        document.querySelectorAll('tbody tr').forEach(row => {
            const statusBadge = row.querySelector('.badge');
            if (statusBadge && statusBadge.textContent.trim().toLowerCase() === 'pending') {
                const deleteForm = row.querySelector('form[action*="destroy"]');
                if (deleteForm) {
                    pendingRequests.push(deleteForm);
                }
            }
        });

        if (pendingRequests.length === 0) {
            alert('No pending requests found to delete.');
            return;
        }

        // Delete each pending request one by one
        let deletedCount = 0;
        let totalToDelete = pendingRequests.length;

        function deleteNextRequest() {
            if (deletedCount < totalToDelete) {
                const form = pendingRequests[deletedCount];

                // Create a temporary form submission to handle redirects properly
                const tempForm = document.createElement('form');
                tempForm.method = form.method;
                tempForm.action = form.action;
                tempForm.style.display = 'none';

                // Copy all inputs from original form
                form.querySelectorAll('input').forEach(input => {
                    tempForm.appendChild(input.cloneNode(true));
                });

                document.body.appendChild(tempForm);
                tempForm.submit();

                deletedCount++;

                // Wait a bit before deleting next one to avoid conflicts
                setTimeout(deleteNextRequest, 500);
            } else {
                // All deletions completed
                alert(`Successfully deleted ${deletedCount} pending requests. Refreshing page...`);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        }

        deleteNextRequest();
    }
}
</script>
@endsection
