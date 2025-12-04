@extends('layouts.app')

@section('content')
<div class="section container">
     <div class="section-header">
            <h1>Edit Employee</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ url('/employees-management') }}">Employees</a></div>
                <div class="breadcrumb-item">Edit Employee</div>
            </div>
        </div>
    <div class="row justify-content-center mt-3">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header justify-content-center mb-4">
                    <h5 class="mb-0">Edit Employee</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs justify-content-center" id="employeeTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Basic Information</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab">Job Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab">Salary & Payroll Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button" role="tab">Document Uploads</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">Other Details</button>
                        </li>
                    </ul>
                    <form method="POST" action="{{ route('employees.management.update', $employee->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="tab-content pt-3" id="employeeTabContent">
                            <!-- Basic Information Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Employee Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $employee->name) }}"  autofocus>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="parent_name" class="form-label">Father’s / Mother’s Name<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('parent_name') is-invalid @enderror" id="parent_name" name="parent_name" value="{{ old('parent_name', $employee->parent_name) }}">
                                          @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gender <span class="text-danger">*</span></label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" {{ old('gender', $employee->gender) == 'male' ? 'checked' : '' }} >
                                            <label class="form-check-label" for="gender_male">Male</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female" {{ old('gender', $employee->gender) == 'female' ? 'checked' : '' }} >
                                            <label class="form-check-label" for="gender_female">Female</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="gender_other" value="other" {{ old('gender', $employee->gender) == 'other' ? 'checked' : '' }} >
                                            <label class="form-check-label" for="gender_other">Other</label>
                                        </div>
                                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('dob') is-invalid @enderror" id="dob" name="dob" value="{{ old('dob', $employee->dob?->format('Y-m-d')) }}" >
                                        @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="marital_status" class="form-label">Marital Status</label>
                                        <select class="form-select @error('marital_status') is-invalid @enderror" id="marital_status" name="marital_status" >
                                            <option value="">Select</option>
                                            <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                            <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                            <option value="divorced" {{ old('marital_status', $employee->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                            <option value="widowed" {{ old('marital_status', $employee->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                        </select>
                                        @error('marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" maxlength="10" name="contact_number" value="{{ old('contact_number', $employee->contact_number ?? $employee->phone) }}" >
                                        @error('contact_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="personal_email" class="form-label">Personal Email ID <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('personal_email') is-invalid @enderror" id="personal_email" name="personal_email" value="{{ old('personal_email', $employee->email) }}" >
                                        @error('personal_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="official_email" class="form-label">Official Email ID</label>
                                        <input type="email" class="form-control" id="official_email" name="official_email" value="{{ old('official_email', $employee->official_email) }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="current_address" class="form-label">Current Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('current_address') is-invalid @enderror" id="current_address" name="current_address" rows="2" >{{ old('current_address', $employee->current_address) }}</textarea>
                                        @error('current_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="permanent_address" class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('permanent_address') is-invalid @enderror" id="permanent_address" name="permanent_address" rows="2" >{{ old('permanent_address', $employee->permanent_address) }}</textarea>
                                        @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('employees.management.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to List
                                    </a>
                                    <button type="button" class="btn btn-primary btn-next-tab" data-next-tab="#job">
                                        <i class="fas fa-arrow-right-to-file me-1"></i> Next
                                    </button>
                                </div>
                            </div>
                            <!-- Job Details Tab -->
                            <div class="tab-pane fade" id="job" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="employee_code" class="form-label">Employee Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('employee_code') is-invalid @enderror" id="employee_code" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" readonly >
                                        <div id="employee_code_error" class="text-danger small mt-1"></div>
                                        @error('employee_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="joining_date" class="form-label">Date of Joining <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('joining_date') is-invalid @enderror" id="joining_date" name="joining_date" value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}" >
                                        @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                        <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" >
                                            <option value="">Select Department</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <!-- <div class="col-md-6 mb-3">
                                        <label for="designation_id" class="form-label">Designation <span class="text-danger">*</span></label>
                                        <select class="form-select" id="designation_id" name="designation_id" >
                                            <option value="">Select Designation</option>
                                            @foreach($designations as $designation)
                                                <option value="{{ $designation->id }}" {{ old('designation_id', $employee->designation_id) == $designation->id ? 'selected' : '' }}>{{ $designation->title }}</option>
                                            @endforeach
                                        </select>
                                    </div> -->
                                    <div class="col-md-6 mb-3">
                                        <label for="designation_id" class="form-label">Designation <span class="text-danger">*</span></label>
                                        <select class="form-select @error('designation_id') is-invalid @enderror" id="designation_id" name="designation_id" >
                                            <option value="">Select Designation</option>
                                            @foreach($designations as $designation)
                                                <option value="{{ $designation->id }}"
                                                    {{ old('designation_id', $employee->designation_id) == $designation->id ? 'selected' : '' }}>
                                                    {{ $designation->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                 </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location / Branch <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $employee->location) }}" >
                                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="employment_type_id" class="form-label">Employment Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('employment_type_id') is-invalid @enderror" id="employment_type_id" name="employment_type_id" >
                                            <option value="">Select Type</option>
                                            @foreach($employmentTypes ?? [] as $type)
                                                <option value="{{ $type->id }}" {{ old('employment_type_id', $employee->employment_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('employment_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="probation_period" class="form-label">Probation Period (Months)</label>
                                        <input type="number" class="form-control" id="probation_period" name="probation_period" value="{{ old('probation_period', $employee->probation_period) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="reporting_manager" class="form-label">Reporting Manager <span class="text-danger">*</span></label>
                                        <select class="form-select @error('reporting_manager') is-invalid @enderror" id="reporting_manager" name="reporting_manager" >
                                            <option value="">Select Manager</option>
                                            @foreach($managers as $manager)
                                                <option value="{{ $manager->id }}" {{ old('reporting_manager', $employee->reporting_manager_id) == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('reporting_manager')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" >
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id', $employee->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('employees.management.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to List
                                    </a>
                                    <button type="button" class="btn btn-primary btn-next-tab" data-next-tab="#salary">
                                        <i class="fas fa-arrow-right-to-file me-1"></i> Next
                                    </button>
                                </div>
                            </div>
                            <!-- Salary & Payroll Details Tab -->
                            <div class="tab-pane fade" id="salary" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ctc" class="form-label">CTC (Cost to Company) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control @error('ctc') is-invalid @enderror" id="ctc" name="ctc" value="{{ old('ctc', $employee->currentSalary->ctc ?? '') }}" >
                                        @error('ctc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="basic_salary" class="form-label">Basic Salary <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control @error('basic_salary') is-invalid @enderror" id="basic_salary" name="basic_salary" value="{{ old('basic_salary', $employee->currentSalary->basic_salary ?? '') }}" >
                                        @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" value="{{ old('bank_name', $employee->currentSalary->bank_name ?? '') }}" >
                                        @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number', $employee->currentSalary->account_number ?? '') }}" >
                                    </div>
                                     </div>
                                    <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ifsc_code" class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase @error('ifsc_code') is-invalid @enderror" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $employee->currentSalary->ifsc_code ?? '') }}" style="text-transform:uppercase" >
                                        @error('ifsc_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>


                                    <div class="col-md-6 mb-3">
                                        <label for="pan_number" class="form-label">PAN Card Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase @error('pan_number') is-invalid @enderror" id="pan_number" name="pan_number" maxlength="10" value="{{ old('pan_number', $employee->currentSalary->pan_number ?? '') }}" style="text-transform:uppercase" >
                                        @error('pan_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="form-text text-muted">Format: AAAAA9999A (5 letters, 4 digits, 1 letter)</small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('employees.management.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to List
                                    </a>
                                    <button type="button" class="btn btn-primary btn-next-tab" data-next-tab="#docs">
                                        <i class="fas fa-arrow-right-to-file me-1"></i> Next
                                    </button>
                                </div>
                            </div>
                            <!-- Document Uploads Tab -->
                            <div class="tab-pane fade" id="docs" role="tabpanel">
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
                                        <div class="dropzone-box mt-2 dz-dotted" style="border: 2px dotted #6c757d; transition: border-color 0.2s; cursor: pointer; min-height: 180px; display: flex !important; flex-direction: column; justify-content: center; align-items: center; background-color: #f8f9fa;">
                                            <div class="dz-message" style="cursor: pointer;">
                                                <div class="dz-text">Click or drag files to upload</div>
                                            </div>
                                            <input type="file" name="{{ $field }}[]" multiple class="form-control d-none" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf">
                                            <div class="dz-preview" style="margin-top: 10px; display: flex; flex-direction: column; align-items: center;"></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('employees.management.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to List
                                    </a>
                                    <button type="button" class="btn btn-primary btn-next-tab" data-next-tab="#other">
                                        <i class="fas fa-arrow-right-to-file me-1"></i> Next
                                    </button>
                                </div>
                            </div>
                            <!-- Other Details Tab -->
                            <div class="tab-pane fade" id="other" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact" class="form-label">Emergency Contact Number</label>
                                        <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror" id="emergency_contact" maxlength="10" name="emergency_contact" value="{{ old('emergency_contact', $employee->emergency_contact) }}">
                                        @error('emergency_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact_relation" class="form-label">Emergency Contact Relation</label>
                                        <input type="text" class="form-control @error('emergency_contact_relation') is-invalid @enderror" id="emergency_contact_relation" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation) }}">
                                        @error('emergency_contact_relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                        <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                                        @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="blood_group" class="form-label">Blood Group</label>
                                        {{-- <input type="text" class="form-control" id="blood_group" name="blood_group" value="{{ old('blood_group') }}"> --}}
                                        <select class="form-select" id="blood_group" name="blood_group">
                                            <option value="">Select Blood Group</option>
                                            <option value="A+" {{ old('blood_group', $employee->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                            <option value="A-" {{ old('blood_group', $employee->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                            <option value="B+" {{ old('blood_group', $employee->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                            <option value="B-" {{ old('blood_group', $employee->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                            <option value="O+" {{ old('blood_group', $employee->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                            <option value="O-" {{ old('blood_group', $employee->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                                            <option value="AB+" {{ old('blood_group', $employee->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                            <option value="AB-" {{ old('blood_group', $employee->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="nominee_details" class="form-label">Nominee Details (For PF/ESIC)</label>
                                    <textarea class="form-control" id="nominee_details" name="nominee_details" rows="2">{{ old('nominee_details', $employee->nominee_details) }}</textarea>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('employees.management.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to List
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Employee
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .dz-dotted {
        border: 2px dotted #6c757d !important;
        transition: border-color 0.2s;
        cursor: pointer;
        min-height: 180px;
        display: flex !important;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
    }
    .dz-dotted.border-primary {
        border-color: #0d6efd !important;
    }
    .dz-message i.fas.fa-cloud-upload_alt {
        color: #212529;
    }
    .dz-preview {
        margin-top: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .dz-preview img {
        width: 50%;
        max-height: 180px;
        margin-bottom: 8px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        object-fit: contain;
    }
    .dz-preview embed {
        width: 100%;
        max-height: 180px;
        margin-bottom: 8px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        object-fit: contain;
    }
    .dz-remove-btn {
        margin-top: 5px;
        color: #dc3545;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
    }
</style>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check for validation errors and open the tab with the first error
        const invalidFeedbacks = document.querySelectorAll('.invalid-feedback');
        if (invalidFeedbacks.length > 0) {
            // Enable all tabs if there are errors
            document.querySelectorAll('#employeeTab button.nav-link').forEach(btn => {
                btn.classList.remove('disabled');
                btn.removeAttribute('aria-disabled');
                btn.removeAttribute('tabindex');
            });

            // Find the first error and activate its tab
            const firstError = invalidFeedbacks[0];
            const tabPane = firstError.closest('.tab-pane');
            if (tabPane) {
                const tabId = tabPane.id;
                const tabButton = document.querySelector(`[data-bs-target="#${tabId}"]`);
                if (tabButton) {
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                }
            }
        }
    });

    // correct designations  loads JS object
    let allDesignations = @json($designations);

    document.addEventListener("DOMContentLoaded", function () {
        let deptSelect = document.getElementById('department_id');
        let designationSelect = document.getElementById('designation_id');
        let oldDesignation = "{{ old('designation_id', $employee->designation_id) }}";

        function filterDesignations(deptId, preselected = null) {
            designationSelect.innerHTML = '<option value="">Select Designation</option>';
            let filtered = allDesignations.filter(d => d.department_id == deptId);

            filtered.forEach(function(d) {
                let option = document.createElement('option');
                option.value = d.id;
                option.text = d.title;
                if (preselected && preselected == d.id) option.selected = true;
                designationSelect.add(option);
            });
        }

        // Department change
        deptSelect.addEventListener('change', function () {
            filterDesignations(this.value);
        });

        // Page load (old value)
        if (deptSelect.value) {
            filterDesignations(deptSelect.value, oldDesignation);
        }
    });

    // Tab navigation on Next button click
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-next-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var nextTab = btn.getAttribute('data-next-tab');
                if (nextTab) {
                    var nextTabBtn = document.querySelector('[data-bs-target="' + nextTab + '"]');
                    if (nextTabBtn) {
                        nextTabBtn.click();
                    }
                }
            });
        });
    });
    // Disable DOB dates less than 18 years from now
    document.addEventListener('DOMContentLoaded', function() {
        var dob = document.getElementById('dob');
        if (dob) {
            var today = new Date();
            var minDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
            // dob.max = minDate.toISOString().split('T')[0];
        }
        // Auto-uppercase IFSC and PAN
        document.getElementById('ifsc_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
        document.getElementById('pan_number').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    });
    // Drag and Drop for Document Uploads with remove button and larger preview
    document.querySelectorAll('.dropzone-box').forEach(function(box) {
        var input = box.querySelector('input[type="file"]');
        var dzMsg = box.querySelector('.dz-message');
        var preview = box.querySelector('.dz-preview');
        dzMsg.addEventListener('click', function() { input.click(); });
        dzMsg.addEventListener('dragover', function(e) { e.preventDefault(); dzMsg.classList.add('border-primary'); });
        dzMsg.addEventListener('dragleave', function(e) { e.preventDefault(); dzMsg.classList.remove('border-primary'); });
        dzMsg.addEventListener('drop', function(e) {
            e.preventDefault();
            dzMsg.classList.remove('border-primary');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showPreview(input, preview, input, dzMsg);
            }
        });
        input.addEventListener('change', function() { showPreview(input, preview, input, dzMsg); });
    });
    function showPreview(input, preview, fileInput, dzMsg) {
        preview.innerHTML = '';
        if (input.files && input.files.length) {
            Array.from(input.files).forEach(function(file, idx) {
                var ext = file.name.split('.').pop().toLowerCase();
                var allowed = ["jpg","jpeg","png","gif","bmp","webp","pdf"];
                if (!allowed.includes(ext)) {
                    preview.innerHTML += '<div class="text-danger">Only image and PDF files are allowed: ' + file.name + '</div>';
                    return;
                }
                var fileContainer = document.createElement('div');
                fileContainer.style.position = 'relative';
                fileContainer.style.display = 'flex';
                fileContainer.style.flexDirection = 'column';
                fileContainer.style.alignItems = 'center';
                fileContainer.style.marginBottom = '10px';
                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'dz-remove-btn';
                removeBtn.innerHTML = '<i class="fas fa-times-circle"></i> Remove';
                removeBtn.onclick = function(e) {
                    e.stopPropagation();
                    // Remove the file from the FileList
                    var dt = new DataTransfer();
                    Array.from(input.files).forEach(function(f, i) {
                        if (i !== idx) dt.items.add(f);
                    });
                    input.files = dt.files;
                    showPreview(input, preview, fileInput, dzMsg);
                };
                if (["jpg","jpeg","png","gif","bmp","webp"].includes(ext)) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        fileContainer.innerHTML = '<img src="'+e.target.result+'" class="img-thumbnail"> <div>'+file.name+'</div>';
                        fileContainer.appendChild(removeBtn);
                        preview.appendChild(fileContainer);
                    };
                    reader.readAsDataURL(file);
                } else if (ext === "pdf") {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        fileContainer.innerHTML = '<embed src="'+e.target.result+'#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" width="180" height="180" style="border-radius:8px;border:1px solid #dee2e6;object-fit:contain;"> <div>'+file.name+'</div>';
                        fileContainer.appendChild(removeBtn);
                        preview.appendChild(fileContainer);
                    };
                    reader.readAsDataURL(file);
                }
            });
            dzMsg.querySelector('.dz-text').style.display = 'none';
        } else {
            dzMsg.querySelector('.dz-text').style.display = '';
        }
    }
    // Fetch employee code on employment type change
    $(document).ready(function() {
        $('#employment_type_id').on('change', function() {
            var employmentTypeId = $(this).val();
            var companyId = @json($company->id ?? null);
            if (employmentTypeId && companyId) {
                $.ajax({
                    url: '/employees-management/next-employee-code',
                    method: 'GET',
                    data: { employment_type_id: employmentTypeId, company_id: companyId },
                    success: function(res) {
                        if (res.code) {
                            $('#employee_code').val(res.code);
                            $('#employee_code_error').text('');
                        } else {
                            $('#employee_code').val('');
                            $('#employee_code_error').text('No prefix found for this type.');
                        }
                    },
                    error: function() {
                        $('#employee_code').val('');
                        $('#employee_code_error').text('Error fetching code.');
                    }
                });
            } else {
                $('#employee_code').val('');
                $('#employee_code_error').text('');
            }
        });

        // Show tab containing first invalid field on form submit (robust, minimal manipulation)
        $('form').on('submit', function(e) {
            var $form = $(this);
            var invalid = $form.find(':invalid').first();
            if (invalid.length) {
                var $tabPane = invalid.closest('.tab-pane');
                if ($tabPane.length && !$tabPane.hasClass('active')) {
                    var tabId = '#' + $tabPane.attr('id');
                    var $tabBtn = $('[data-bs-target="' + tabId + '"]');
                    if ($tabBtn.length) {
                        $tabBtn.tab('show');
                        setTimeout(function() {
                            if (invalid.is(':disabled')) invalid.prop('disabled', false);
                            if (invalid.is(':hidden')) invalid.show();
                            invalid.focus();
                        }, 200);
                    }
                } else {
                    invalid.focus();
                }
                e.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection
