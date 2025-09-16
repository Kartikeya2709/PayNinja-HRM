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
                <a href="{{ route('admin.payroll.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Generate New Payroll
                </a>
            </div>
    </div>

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
                                <td>{{ $payroll->id }}</td>
                                <td>{{ $payroll->employee->user->name ?? 'N/A' }} ({{ $payroll->employee->employee_id ?? 'N/A'}}})</td>
                                <td>{{ $payroll->pay_period_start->format('M d, Y') }} - {{ $payroll->pay_period_end->format('M d, Y') }}</td>
                                <td>{{-- Format as currency --}} {{ number_format($payroll->gross_salary, 2) }}</td>
                                <td>{{-- Format as currency --}} {{ number_format($payroll->net_salary, 2) }}</td>
                                <td><span class="badge badge-{{ $payroll->status == 'paid' ? 'success' : ($payroll->status == 'processed' ? 'info' : 'warning') }}">{{ ucfirst($payroll->status) }}</span></td>
                                <td>{{ $payroll->processor->name ?? 'System' }}</td>
                                <td>
                                    <a href="{{ route('admin.payroll.show', $payroll->id) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- Add edit/delete/process buttons later --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No payroll records found.</td>
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
{{-- Add any specific JS for this page, e.g., for datatables --}}
@endpush
