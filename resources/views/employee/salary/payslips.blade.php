@extends('layouts.app')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>My Payslips</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">My Payslips</a></div>
            </div>
        </div>





        <div class="row">
            <div class="col-12 px-0">
                <div class="card">
                    <div class="card-header margin-bottom">
                        <h3 class="card-title">Payroll History</h3>
                        <!-- <div class="card-tools">
                                <a href="{{-- route('employee.salary.details') --}}" class="btn btn-default btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Salary Details
                                </a>
                            </div> -->
                    </div>
                    <div class="card-body">
                        @if($payslips->isEmpty())
                        <div class="alert alert-info">
                            <i class="icon fas fa-info"></i> No payslips available yet.
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Pay Period</th>
                                        <th>Status</th>
                                        <th>Gross Salary</th>
                                        <th>Deductions</th>
                                        <th>Net Salary</th>
                                        <th>Payment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payslips as $payslip)
                                    <tr>
                                        <td>{{ $payslip['pay_period'] }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payslip['status_color'] }}">
                                                {{ ucfirst($payslip['status']) }}
                                            </span>
                                        </td>
                                        <td class="text-right">₹{{ number_format($payslip['gross_salary'], 2) }}</td>
                                        <td class="text-right">₹{{ number_format($payslip['total_deductions'], 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            ₹{{ number_format($payslip['net_salary'], 2) }}</td>
                                        <td>{{ $payslip['payment_date'] ? \Carbon\Carbon::parse($payslip['payment_date'])->format('d M, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('employee.payroll.show', $payslip['id']) }}"
                                                    class="btn btn-sm btn-info" title="View Payslip" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('employee.payroll.download', $payslip['id']) }}"
                                                    class="btn btn-sm btn-success" title="Download Payslip">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $payrolls->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
</div>
</section>
</div>

<style>
.card-outline {
    border-top: 3px solid #007bff !important;
    margin-bottom: 20px;
}

.badge {
    font-size: 0.8rem;
    padding: 0.35em 0.65em;
}

.table th {
    white-space: nowrap;
}

.btn-group .btn {
    margin: 0 2px;
}

.table td {
    vertical-align: middle;
}
</style>
@endsection