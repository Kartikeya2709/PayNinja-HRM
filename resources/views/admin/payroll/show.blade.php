@extends('layouts.app')

@section('title', 'Payroll Details - ID: {{ $payroll->id }}')

@section('content_header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Payroll Details <small class="text-muted">#{{ $payroll->id }}</small></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll Records</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="invoice p-3 mb-3">
        <!-- title row -->
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-file-invoice-dollar"></i> Payroll Statement
                    <small class="float-right">Date: {{ $payroll->created_at->format('M d, Y') }}</small>
                </h4>
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Payslip Information</h6>
                    <div>
                        {{-- Action buttons like Print, Process, Mark as Paid --}}
                        @if ($payroll->status === 'pending' || $payroll->status === 'processing')
                            {{-- <a href="{{ route('admin.payroll.edit', $payroll->id) }}" class="btn btn-warning btn-sm">Edit</a> --}}
                            {{-- <form action="{{ route('admin.payroll.process', $payroll->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm">Process Payroll</button>
                    </form> --}}
                        @endif
                        @if ($payroll->status === 'processed')
                            {{-- <form action="{{ route('admin.payroll.mark-as-paid', $payroll->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Mark as Paid</button>
                    </form> --}}
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Employee:</strong> {{ $payroll->employee->user->name ?? 'N/A' }} (ID:
                                {{ $payroll->employee->employee_id ?? 'N/A' }})</p>
                            <p><strong>Company:</strong> {{ $payroll->company->name ?? 'N/A' }}</p>
                            <p><strong>Pay Period:</strong> {{ $payroll->pay_period_start->format('M d, Y') }} -
                                {{ $payroll->pay_period_end->format('M d, Y') }}</p>
                            <p><strong>Status:</strong> <span
                                    class="badge badge-{{ $payroll->status == 'paid' ? 'success' : ($payroll->status == 'processed' ? 'info' : 'warning') }}">{{ ucfirst($payroll->status) }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <p><strong>Gross Salary:</strong> {{ number_format($payroll->gross_salary, 2) }}</p>
                            <p><strong>Total Deductions:</strong> {{ number_format($payroll->total_deductions, 2) }}</p>
                            <p><strong>Net Salary:</strong> {{ number_format($payroll->net_salary, 2) }}</p>
                            @if ($payroll->payment_date)
                                <p><strong>Payment Date:</strong> {{ $payroll->payment_date->format('M d, Y') }}</p>
                            @endif
                            <p><strong>Processed By:</strong> {{ $payroll->processor->name ?? 'System' }} on
                                {{ $payroll->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    <hr>
                    <h5>Payroll Breakdown:</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Earnings & Reimbursements</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payroll->items->whereIn('type', ['earning', 'reimbursement', 'bonus', 'overtime']) as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Deductions</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payroll->items->whereIn('type', ['deduction', 'statutory_contribution', 'loan_repayment']) as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($payroll->notes)
                        <hr>
                        <h5>Notes:</h5>
                        <p>{{ $payroll->notes }}</p>
                    @endif

                    @if ($payroll->data_snapshot)
                        <!-- <hr> -->
                        <!-- <h5>Data Snapshot (For Auditing):</h5>
                        @if (is_iterable($payroll->data_snapshot) && count((array) $payroll->data_snapshot) > 0)
                            <dl class="row mt-2" style="font-size: 0.9rem;">
                                @foreach ($payroll->data_snapshot as $key => $value)
                                    <dt class="col-sm-4 text-muted border-bottom pb-1 mb-1">
                                        {{ Str::title(str_replace(['_', '-'], ' ', $key)) }}</dt>
                                    <dd class="col-sm-8 border-bottom pb-1 mb-1">
                                        @if (is_array($value) || is_object($value))
                                            <pre class="mb-0"
                                                style="font-size: 0.85rem; background-color: #f0f0f0; padding: 8px; border-radius: 4px; white-space: pre-wrap; word-break: break-all;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                        @elseif(is_bool($value))
                                            {{ $value ? 'Yes' : 'No' }}
                                        @elseif(is_null($value))
                                            <em class="text-muted">N/A</em>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </dd>
                                @endforeach
                            </dl>
                        @elseif(is_string($payroll->data_snapshot) && !empty(trim($payroll->data_snapshot)))
                            <p class="text-muted mt-2"><em>Raw data snapshot:</em></p>
                            <pre
                                style="font-size: 0.8rem; background-color: #f8f9fa; padding: 10px; border-radius: 5px; white-space: pre-wrap; word-break: break-all;">{{ $payroll->data_snapshot }}</pre>
                        @else
                            <p class="text-muted mt-2"><em>Data snapshot is empty or not in a displayable list format.</em>
                            </p>
                        @endif -->
                    @endif

                </div>
            </div>
        </div>
    </div>
    @endsection
