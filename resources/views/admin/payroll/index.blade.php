@extends('layouts.app')

@section('title', 'Payroll Records')

@section('content_header')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Payroll Records</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ route('home') }}">Dashboard</a>
                </div>
                <div class="breadcrumb-item">
                    <a href="">Payroll Records</a>
                </div>
            </div>
        </div>
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Payroll Records</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Payroll Records</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container">
<section class="section">
        <div class="section-header">
            <h1>Generated Payrolls</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ route('home') }}">Dashboard</a>
                </div>
                <div class="breadcrumb-item">
                    <a href="">Generated Payrolls</a>
                </div>
            </div>
        </div>
    <div class="card">
        <div class="card-header margin-bottom">
            <h3 class="card-title">List of Generated Payrolls</h3>
            <div class="card-tools">
                @if(\App\Models\User::hasAccess('admin/payroll/create', true))
                <a href="{{ route('create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Generate New Payroll
                </a>
                @endif
            </div>
        </div>

        {{-- Bulk Actions Section --}}
        @if($payrolls->whereIn('status', ['pending', 'processed'])->count() > 0)
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-0">Bulk Actions</h6>
                    <small class="text-muted">Select payrolls to approve and mark as paid</small>
                </div>
                <div class="col-md-4 text-right">
                    @if(\App\Models\User::hasAccess('admin/payroll/bulk-approve', true))
                    <button type="button" class="btn btn-success btn-sm" id="bulk-approve-btn" disabled>
                        <i class="fas fa-check"></i> Approve Selected & Mark as Paid
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-header py-3 justify-content-center mb-2">
            <h5 class="m-0">All Payrolls</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            @if($payrolls->whereIn('status', ['pending', 'processed'])->count() > 0)
                            <th>
                                <input type="checkbox" id="select-all" class="form-check-input">
                                <label for="select-all" class="form-check-label ms-1">Select All</label>
                            </th>
                            @endif
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Pay Period</th>
                            <th>Gross Salary</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            <tr>
                                @if($payrolls->whereIn('status', ['pending', 'processed'])->count() > 0)
                                <td>
                                    @if($payroll->status === 'pending' || $payroll->status === 'processed')
                                    <input type="checkbox" class="payroll-checkbox form-check-input" value="{{ $payroll->id }}" id="payroll-{{ $payroll->id }}">
                                    @endif
                                </td>
                                @endif
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $payroll->employee->user->name ?? 'N/A' }} ({{ $payroll->employee->employee_code ?? 'N/A'}})</td>
                                <td>{{ $payroll->pay_period_start->format('M d, Y') }} - {{ $payroll->pay_period_end->format('M d, Y') }}</td>
                                <td>{{-- Format as currency --}} {{ number_format($payroll->gross_salary, 2) }}</td>
                                <td>{{-- Format as currency --}} {{ number_format($payroll->net_salary, 2) }}</td>
                                <td><span class="badge badge-{{ $payroll->status == 'paid' ? 'success' : ($payroll->status == 'processed' ? 'info' : 'warning') }}">{{ ucfirst($payroll->status) }}</span></td>
                                <td>{{ $payroll->processor->name ?? 'System' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if(\App\Models\User::hasAccess('admin/payroll/{payroll}', true))
                                        <a href="{{ route('show', $payroll->id) }}" class="btn btn-info btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                        @if($payroll->status !== 'paid' && \App\Models\User::hasAccess('admin/payroll/{payroll}/edit', true))
                                        <a href="{{ route('edit', $payroll->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @if($payroll->status !== 'paid' && \App\Models\User::hasAccess('admin/payroll/{payroll}/destroy', true))
                                        <form action="{{ route('destroy', $payroll->id)  }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payroll record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if(\App\Models\User::hasAccess('admin/payroll/{payroll}/mark-as-paid', true))
                                        @if(($payroll->status === 'pending' || $payroll->status === 'processed') )
                                        <form action="{{ route('markAsPaid', $payroll->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm" title="Mark as Paid">
                                                <i class="fas fa-check"></i> Pay
                                            </button>
                                        </form>
                                        @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $payrolls->whereIn('status', ['pending', 'processed'])->count() > 0 ? '9' : '8' }}" class="text-center">No payroll records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $payrolls->links() }}
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const payrollCheckboxes = document.querySelectorAll('.payroll-checkbox');
    const bulkApproveBtn = document.getElementById('bulk-approve-btn');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            payrollCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkApproveButton();
        });
    }

    // Individual checkbox change
    payrollCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkApproveButton();

            // Update select all checkbox state
            const checkedBoxes = document.querySelectorAll('.payroll-checkbox:checked');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = checkedBoxes.length === payrollCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < payrollCheckboxes.length;
            }
        });
    });

    function updateBulkApproveButton() {
        const checkedBoxes = document.querySelectorAll('.payroll-checkbox:checked');
        if (bulkApproveBtn) {
            bulkApproveBtn.disabled = checkedBoxes.length === 0;
        }
    }

    // Bulk approve functionality
    if (bulkApproveBtn) {
        bulkApproveBtn.addEventListener('click', function() {
            const selectedPayrolls = Array.from(document.querySelectorAll('.payroll-checkbox:checked')).map(cb => cb.value);

            if (selectedPayrolls.length === 0) {
                alert('Please select at least one payroll to approve.');
                return;
            }

            if (confirm(`Are you sure you want to approve and mark as paid ${selectedPayrolls.length} payroll(s)?`)) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("bulkApprove") }}';

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add selected payroll IDs
                selectedPayrolls.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'payroll_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>
@endpush
