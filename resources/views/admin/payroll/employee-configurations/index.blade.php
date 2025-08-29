@extends('layouts.app')

@section('title', 'Employee Payroll Configurations')

@section('content')
   
<div class="container">
     <section class="section">
        <div class="section-header">
            <h1>Employee Payroll Configurations</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ route('home') }}">Dashboard</a>
                </div>
                <div class="breadcrumb-item">
                    <a href="">Employee Payroll Configurations</a>
                </div>
            </div>
        </div>
 <div class="section">
    
       
             
                   
                    <p class="mb-2">Manage Cost To Company (CTC) and assigned beneficiary badges for employees.</p>
              
                {{-- Optional: Add a link back or other actions here --}}
           

        @if(session('success'))
            <div class="col-lg-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="col-lg-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Employees List</h5>
                </div>
                <div class="card-body">
                    @if($employees->isEmpty())
                        <div class="text-center">
                            <p>No employees found in your company.</p>
                            {{-- Optional: Link to add employees if applicable --}}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Current CTC</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employees as $employee)
                                        <tr>
                                            <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                                            <td>{{ $employee->user->name ?? 'N/A' }}</td>
                                            <td>{{ $employee->user->email ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $currentSalary = $employee->employeeSalaries->where('is_current', true)->first();
                                                @endphp
                                                @if($currentSalary && $currentSalary->ctc !== null)
                                                    {{ $employee->company->default_currency ?? '₹' }} {{ number_format($currentSalary->ctc, 2) }}
                                                    @if($currentSalary->effective_from)
                                                        <br><small class="text-muted">From: {{ $currentSalary->effective_from->format('d M, Y') }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Not Set</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.payroll.employee-configurations.edit', $employee->id) }}" class="btn btn-sm btn-soft-primary"><i class="fa-solid fa-gear me-1"></i> Configure Payroll</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $employees->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection
