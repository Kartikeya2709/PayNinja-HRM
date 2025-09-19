@extends('layouts.app')

@section('content')
     <div class="container">
    <section class="section">
            <div class="section-header">
                <h1>Edit Employee</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="">Edit Employee</a></div>
                </div>
            </div>
             <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header justify-content-center">
        <h5>Edit Employee for {{ $company->name }}</h5>
</div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 px-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('company.employees.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-4 mt-2">
                <label for="name">Employee Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $employee->user ? $employee->user->name : $employee->name) }}" required>
            </div>

            <div class="form-group mb-4">
                <label for="email">Employee Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $employee->user ? $employee->user->email : $employee->email) }}" required>
            </div>

            <div class="form-group mb-4">
                <label for="department">Department:</label>
                <select class="form-control" id="department" name="department_id" required>
                    <option value="">Select Department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-4">
                <label for="designation">Designation:</label>
                <select class="form-control" id="designation" name="designation_id" required>
                    <option value="">Select Designation</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}" {{ $employee->designation_id == $designation->id ? 'selected' : '' }}>
                            {{ $designation->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-4">
                <label for="phone">Phone Number:</label>
                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}">
            </div>

            <div class="form-group mb-4">
                <label for="dob">Date of Birth:</label>
                <input type="date" class="form-control" id="dob" name="dob" value="{{ old('dob', optional($employee->employeeDetail)->dob) }}">
            </div>

            <div class="form-group mb-4">
                <label for="gender">Gender:</label>
                <select class="form-control" id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" {{ optional($employee->employeeDetail)->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ optional($employee->employeeDetail)->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ optional($employee->employeeDetail)->gender == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label for="emergency_contact">Emergency Contact:</label>
                <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', optional($employee->employeeDetail)->emergency_contact) }}">
            </div>

            <div class="form-group mb-4">
                <label for="joining_date">Joining Date:</label>
                <input type="date" class="form-control" id="joining_date" name="joining_date" value="{{ old('joining_date', optional($employee->employeeDetail)->joining_date) }}" required>
            </div>

            <div class="form-group mb-4">
                <label for="employment_type">Employment Type:</label>
                <select class="form-control" id="employment_type" name="employment_type" required>
                    <option value="">Select Employment Type</option>
                    <option value="permanent" {{ optional($employee->employeeDetail)->employment_type == 'permanent' ? 'selected' : '' }}>Permanent</option>
                    <option value="contract" {{ optional($employee->employeeDetail)->employment_type == 'contract' ? 'selected' : '' }}>Contract</option>
                    <option value="intern" {{ optional($employee->employeeDetail)->employment_type == 'intern' ? 'selected' : '' }}>Intern</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label for="address">Address:</label>
                <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $employee->address) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Update Employee</button>
            <a href="{{ route('company.employees.index') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
</div>
</div>
</div>
</div>
@endsection
