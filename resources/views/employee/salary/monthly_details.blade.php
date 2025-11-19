@extends('layouts.app')

@php
    $startDate = request('start_date') ? \Carbon\Carbon::parse(request('start_date')) : \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
    $endDate = request('end_date') ? \Carbon\Carbon::parse(request('end_date')) : \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
    $period = $startDate->format('d M, Y') . ' to ' . $endDate->format('d M, Y');
    $workingDays = $startDate->diffInDays($endDate) + 1;
@endphp

@section('title', 'Salary Details - ' . $month)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Salary Details - {{ $month }}
                        <small class="text-muted">({{ $period }})</small>
                    </h5>
                    {{-- <a href="{{ route('employee.salary.details') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Salary
                    </a> --}}
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Salary Breakdown</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Description</th>
                                                    <th class="text-right">Amount (₹)</th>
                                                    <th class="text-right">Days</th>
                                                    <th class="text-right">Total (₹)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Basic Salary (per day)</td>
                                                    <td class="text-right">{{ number_format($perDayBasic, 2) }}</td>
                                                    <td class="text-right">{{ $presentDays }}</td>
                                                    <td class="text-right">{{ number_format($perDayBasic * $presentDays, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>HRA ({{ $salary->hra_percentage }}% of Basic)</td>
                                                    <td class="text-right">{{ number_format($perDayHra, 2) }}</td>
                                                    <td class="text-right">{{ $presentDays }}</td>
                                                    <td class="text-right">{{ number_format($perDayHra * $presentDays, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>DA ({{ $salary->da_percentage }}% of Basic)</td>
                                                    <td class="text-right">{{ number_format($perDayDa, 2) }}</td>
                                                    <td class="text-right">{{ $presentDays }}</td>
                                                    <td class="text-right">{{ number_format($perDayDa * $presentDays, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Other Allowances (per day)</td>
                                                    <td class="text-right">{{ number_format($perDayAllowances, 2) }}</td>
                                                    <td class="text-right">{{ $presentDays }}</td>
                                                    <td class="text-right">{{ number_format($perDayAllowances * $presentDays, 2) }}</td>
                                                </tr>
                                                <tr class="table-active">
                                                    <th colspan="3" class="text-right">Total Earnings:</th>
                                                    <th class="text-right">₹{{ number_format($totalEarnings, 2) }}</th>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-right"><strong>Deductions:</strong></td>
                                                    <td class="text-right text-danger">- ₹{{ number_format($deductions, 2) }}</td>
                                                </tr>
                                                <tr class="table-active">
                                                    <th colspan="3" class="text-right">Net Salary Payable:</th>
                                                    <th class="text-right text-primary">₹{{ number_format($netSalary, 2) }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Month Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="text-muted">Attendance</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Working Days:</span>
                                            <span class="font-weight-bold">{{ $workingDays }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Present Days:</span>
                                            <span class="text-success font-weight-bold">{{ $presentDays }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Leaves:</span>
                                            <span class="text-warning font-weight-bold">{{ $leaveDays }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Absent Days:</span>
                                            <span class="text-danger font-weight-bold">{{ $absentDays }}</span>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="mb-3">
                                        <h6 class="text-muted">Salary Details</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Basic Salary:</span>
                                            <span>₹{{ number_format($salary->basic_salary, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>HRA ({{ $salary->hra_percentage ?? 0 }}%):</span>
                                            <span>₹{{ number_format($salary->hra, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>DA ({{ $salary->da_percentage ?? 0 }}%):</span>
                                            <span>₹{{ number_format($salary->da, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Other Allowances:</span>
                                            <span>₹{{ number_format($salary->other_allowances, 2) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button class="btn btn-primary btn-block" onclick="window.print()">
                                            <i class="fas fa-print mr-2"></i>Print Payslip
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Payment Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Payment Method:</strong> Bank Transfer</p>
                                    <p class="mb-1"><strong>Account Number:</strong> XXXX-XXXX-{{ substr($employee->bank_account_number, -4) ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Bank Name:</strong> {{ $employee->bank_name ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>IFSC Code:</strong> {{ $employee->ifsc_code ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Payment Date:</strong> {{ now()->endOfMonth()->format('d M, Y') }}</p>
                                    <p class="mb-1"><strong>Payment Status:</strong> <span class="badge badge-success">Processed</span></p>
                                    <p class="mb-1"><strong>Payment Reference:</strong> SAL-{{ strtoupper(Str::random(8)) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none !important;
        }
        .no-print, .no-print * {
            display: none !important;
        }
        .card-header {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
        border: none;
        border-radius: 0.5rem;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1rem 1.25rem;
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #6c757d;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@endsection
