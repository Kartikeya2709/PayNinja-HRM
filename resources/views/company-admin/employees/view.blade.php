@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">View Employee Details</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="employeeTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">Basic Information</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab" aria-controls="job" aria-selected="false">Job Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab" aria-controls="salary" aria-selected="false">Salary & Payroll Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button" role="tab" aria-controls="docs" aria-selected="false">Document Uploads</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab" aria-controls="other" aria-selected="false">Other Details</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3" id="employeeTabContent">
                        <!-- Basic Information Tab -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employee Full Name</label>
                                    <input type="text" class="form-control" value="{{ $employee->name }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Father’s / Mother’s Name</label>
                                    <input type="text" class="form-control" value="{{ $employee->parent_name }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($employee->gender) }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" value="{{ $employee->dob ? $employee->dob->format('Y-m-d') : '' }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Marital Status</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($employee->marital_status) }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" value="{{ $employee->contact_number ?? $employee->phone }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Personal Email ID</label>
                                    <input type="email" class="form-control" value="{{ $employee->email }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Official Email ID</label>
                                    <input type="email" class="form-control" value="{{ $employee->official_email }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current Address</label>
                                    <textarea class="form-control" rows="2" readonly>{{ $employee->current_address }}</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Permanent Address</label>
                                    <textarea class="form-control" rows="2" readonly>{{ $employee->permanent_address }}</textarea>
                                </div>
                            </div>
                        </div>
                        <!-- Job Details Tab -->
                        <div class="tab-pane fade" id="job" role="tabpanel" aria-labelledby="job-tab">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employee Code</label>
                                    <input type="text" class="form-control" value="{{ $employee->employee_code }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Joining</label>
                                    <input type="date" class="form-control" value="{{ $employee->joining_date ? $employee->joining_date->format('Y-m-d') : '' }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department</label>
                                    <input type="text" class="form-control" value="{{ $employee->department->name ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Designation</label>
                                    <input type="text" class="form-control" value="{{ $employee->designation->title ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location / Branch</label>
                                    <input type="text" class="form-control" value="{{ $employee->location }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Employment Type</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($employee->employment_type) }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Probation Period (Months)</label>
                                    <input type="text" class="form-control" value="{{ $employee->probation_period }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Reporting Manager</label>
                                    <input type="text" class="form-control" value="{{ $employee->reportingManager->name ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- Salary & Payroll Details Tab -->
                        <div class="tab-pane fade" id="salary" role="tabpanel" aria-labelledby="salary-tab">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CTC (Cost to Company)</label>
                                    <input type="text" class="form-control" value="{{ $employee->currentSalary->ctc ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Basic Salary</label>
                                    <input type="text" class="form-control" value="{{ $employee->currentSalary->basic_salary ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" value="{{ $employee->currentSalary->bank_name ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" class="form-control" value="{{ $employee->currentSalary->account_number ?? '' }}" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">IFSC Code</label>
                                    <input type="text" class="form-control text-uppercase" value="{{ $employee->currentSalary->ifsc_code ?? '' }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">PAN Card Number</label>
                                    <input type="text" class="form-control text-uppercase" value="{{ $employee->currentSalary->pan_number ?? '' }}" readonly>
                                </div>
                            </div>
                        </div>
                        <!-- Document Uploads Tab -->
                        <div class="tab-pane fade" id="docs" role="tabpanel" aria-labelledby="docs-tab">
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
                        <!-- Other Details Tab -->
                        <div class="tab-pane fade" id="other" role="tabpanel" aria-labelledby="other-tab">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Emergency Contact</label>
                                    <input type="text" class="form-control" value="{{ $employee->emergency_contact }}" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Emergency Contact Relation</label>
                                    <input type="text" class="form-control" value="{{ $employee->emergency_contact_relation }}" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Emergency Contact Name</label>
                                    <input type="text" class="form-control" value="{{ $employee->emergency_contact_name }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Blood Group</label>
                                    <input type="text" class="form-control" value="{{ $employee->blood_group }}" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nominee Details (For PF/ESIC)</label>
                                <textarea class="form-control" rows="2" readonly>{{ $employee->nominee_details }}</textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('company-admin.employees.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
