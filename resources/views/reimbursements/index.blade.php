@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="container">
            <h2 class="mb-4">Reimbursements</h2>
            
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="mb-3">
                <input type="text" class="form-control" id="searchInput" placeholder="Search...">
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Employee</th>
                        <th>Company</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="reimbursementTable">
                    @foreach($reimbursements as $reimbursement)
                        <tr>
                            <td>{{ $reimbursement->title }}</td>
                            <td>{{ $reimbursement->employee->user->name }}</td>
                            <td>{{ $reimbursement->company->name }}</td>
                            <td>₹{{ number_format($reimbursement->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $reimbursement->status === 'pending' ? 'warning' : ($reimbursement->status === 'reporter_approved' ? 'info' : ($reimbursement->status === 'admin_approved' ? 'success' : 'danger')) }}">
                                    {{ ucfirst($reimbursement->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('reimbursements.show', $reimbursement->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-center mt-3">
                {{ $reimbursements->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#reimbursementTable tr');

        searchInput.addEventListener('input', function() {
            const filter = searchInput.value.toLowerCase();
            tableRows.forEach(row => {
                const cells = row.getElementsByTagName('td');
                let match = false;
                Array.from(cells).forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        match = true;
                    }
                });
                row.style.display = match ? '' : 'none';
            });
        });
    </script>
@endsection