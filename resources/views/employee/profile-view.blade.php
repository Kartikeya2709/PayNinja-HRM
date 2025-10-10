@extends('layouts.app')

@section('title', 'Employee Profile')

@section('content')
<div class="container mt-4">
    <div class="row">
        <!-- Left Section -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Full Name:</strong> {{ $employee->name }} </p>
                    <p><strong>Father’s / Mother’s Name:</strong> {{ $employee->parent_name }} </p>
                    <p><strong>Gender:</strong> {{ $employee->gender }}</p>
                    <p><strong>Date of Birth:</strong> {{ $employee->dob->format('Y-m-d') }} </p>
                    <p><strong>Marital Status:</strong> {{ $employee->marital_status }}</p>
                    <p><strong>Contact Number:</strong> {{ $employee->phone }}</p>
                    <p><strong>Personal Email ID:</strong> {{ $employee->email }}</p>
                    <p><strong>Current Address:</strong> {{ $employee->current_address }}</p>
                    <p><strong>Permanent Address:</strong> {{ $employee->permanent_address }}</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Job Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Employee Code:</strong> {{ $employee->employee_code }}</p>
                    <p><strong>Date of Joining:</strong> {{ $employee->joining_date->format('Y-m-d') }} </p>
                    <p><strong>Department:</strong> {{ $department->name }}</p>
                    <p><strong>Designation:</strong> {{ $designation->title }}</p>
                    <p><strong>Location / Branch:</strong> {{ $employee->location }}</p>
                    <p><strong>Employment Type:</strong> {{ $employee->employment_type }}</p>
                    <p><strong>Probation Period (Months):</strong> {{ $employee->probation_period }}</p>
                    <p><strong>Reporting Manager:</strong> {{ $employee->reporting_manager }}</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Birth of Date:</strong> November 4, 1989 <a href="#">Edit</a></p>
                    <p><strong>Gender:</strong> Male <a href="#">Edit</a></p>
                </div>
            </div>
        </div>

        <!-- Right Section -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Salary & Payroll Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>CTC (Cost to Company):</strong> {{ $employee->ctc }}</p>
                    <p><strong>Basic Salary:</strong> {{ $employee->basic_salary }}</p>
                    <p><strong>Bank Name:</strong> {{ $employee->bank_name }}</p>
                    <p><strong>Account Number:</strong> {{ $employee->account_number }}</p>
                    <p><strong>IFSC Code:</strong> {{ $employee->ifsc_code }}</p>
                    <p><strong>PAN Card Number:</strong> {{ $employee->pan_card_number }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Friend List</h5>
                </div>
                <div class="card-body d-flex">
                    <img src="https://bootdey.com/img/Content/avatar/avatar1.png" class="rounded-circle me-2" width="40">
                    <img src="https://bootdey.com/img/Content/avatar/avatar2.png" class="rounded-circle me-2" width="40">
                    <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle me-2" width="40">
                    <img src="https://bootdey.com/img/Content/avatar/avatar4.png" class="rounded-circle me-2" width="40">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
