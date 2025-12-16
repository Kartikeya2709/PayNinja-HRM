@extends('layouts.app') {{-- Or your general authenticated user layout --}}

@section('title', 'My Payslips')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2 class="mb-4">My Payslips</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">Payslip History</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pay Period</th>
                                    <th>Net Salary</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payrolls as $payroll)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $payroll->pay_period_start->format('M d, Y') }} - {{ $payroll->pay_period_end->format('M d, Y') }}</td>
                                        <td>{{-- Format as currency --}} {{ number_format($payroll->net_salary, 2) }}</td>
                                        <td><span class="badge badge-{{ $payroll->status == 'paid' ? 'success' : 'secondary' }}">{{ ucfirst($payroll->status) }}</span></td>
                                        <td>{{ $payroll->payment_date ? $payroll->payment_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('employee.payroll.show', $payroll->id) }}" class="btn btn-info btn-sm">View</a>
                                            @if($payroll->status == 'paid')
                                                <a href="{{ route('employee.payroll.download', $payroll->id) }}" class="btn btn-primary btn-sm">Download PDF</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No payslips found.</td>
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
</div>
@endsection
