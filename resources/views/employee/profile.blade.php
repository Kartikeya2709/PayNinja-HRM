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
                    <p><strong>Reporting Manager:</strong> {{ $employee->reportingManager->name ?? '' }}</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Document Uploads</h5>
                </div>
                <div class="card-body">
                   <div class="row">
                                @php
                                    $docFields = [
                                        'aadhaar_card' => 'Aadhaar Card',
                                        'pan_card' => 'PAN Card',
                                        'passport_photo' => 'Passport Size Photo',
                                        'resume' => 'Resume',
                                        'qualification_certificate' => 'Highest Qualification Certificate',
                                        'experience_letters' => 'Experience Letters',
                                        'relieving_letter' => 'Relieving Letter',
                                        'offer_letter' => 'Offer Letter',
                                        'bank_passbook' => 'Bank Passbook / Cancelled Cheque',
                                        'signed_offer_letter' => 'Signed Offer Letter / Appointment Letter',
                                    ];
                                @endphp
                                @foreach($docFields as $field => $label)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ $label }}</label>
                                    <div>
                                        @if(isset($documents[$field]))
                                            @foreach($documents[$field] as $doc)
                                                @php
                                                    $filePath = asset('storage/' . json_decode($doc->file_path)[0]);
                                                    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                                                @endphp
                                                @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']))
                                                    <img src="{{ $filePath }}" alt="{{ $label }}" class="img-thumbnail" style="max-width: 180px; max-height: 180px;">
                                                @elseif(strtolower($ext) === 'pdf')
                                                    <embed src="{{ $filePath }}#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" width="180" height="180" style="border-radius:8px;border:1px solid #dee2e6;object-fit:contain;">
                                                @else
                                                    <a href="{{ $filePath }}" target="_blank">{{ $label }} Document</a>
                                                @endif
                                            @endforeach
                                        @else
                                            <p class="text-muted">No document uploaded.</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
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
                    <p><strong>CTC (Cost to Company):</strong> {{ $employeeSalary->ctc }}</p>
                    <p><strong>Basic Salary:</strong> {{ $employeeSalary->basic_salary }}</p>
                    <p><strong>Bank Name:</strong> {{ $employeeSalary->bank_name }}</p>
                    <p><strong>Account Number:</strong> {{ $employeeSalary->account_number }}</p>
                    <p><strong>IFSC Code:</strong> {{ $employeeSalary->ifsc_code }}</p>
                    <p><strong>PAN Card Number:</strong> {{ $employeeSalary->pan_number }}</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Other Details</h5>
                      
                </div>
                <div class="card-body ">
                   <p><strong>Emergency Contact:</strong> {{ $employee->emergency_contact_name }}</p>
                      <p><strong>Emergency Contact Relation:</strong> {{ $employee->emergency_contact_relation }}</p>
                      <p><strong>Emergency Contact Name:</strong> {{ $employee->emergency_contact_name }}</p>
                      <p><strong>Blood Group:</strong> {{ $employee->blood_group }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
