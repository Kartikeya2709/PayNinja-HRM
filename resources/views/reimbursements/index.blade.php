@extends('layouts.app')

@section('content')
    <div class="container">
        <section class="section">
            <div class="section-header">
                <h1>Reimbursements</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="">Reimbursements</a></div>
                </div>
            </div>

            <div class="card">
                <div class="card-1">
                    <h5 class="card-title margin-bottom mb-0">Reimbursements</h5>
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                    </div>
                </div>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif



                <div class="Reimburs-table">
                    <table class="table table-bordered Reimbursements-table">
                        <thead>
                            <tr>
                                <th>Serial No.</th>
                                <th>Date</th>
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
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d M Y') }}</td>
                                    <td>{{ $reimbursement->title }}</td>
                                    <td>{{ $reimbursement->employee->user->name }}</td>
                                    <td>{{ $reimbursement->company->name }}</td>
                                    <td>₹{{ number_format($reimbursement->amount, 2) }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $reimbursement->status === 'pending' ? 'warning' : ($reimbursement->status === 'reporter_approved' ? 'info' : ($reimbursement->status === 'admin_approved' ? 'success' : 'danger')) }}">
                                            {{ ucfirst($reimbursement->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('reimbursements.show', $reimbursement->id) }}"
                                            class="btn btn-primary btn-sm">
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

        searchInput.addEventListener('input', function () {
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