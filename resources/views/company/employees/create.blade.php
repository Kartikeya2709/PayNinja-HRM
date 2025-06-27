@extends('layouts.app')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="container card-emp">
        <h2>Create Employee for {{ $company->name }}</h2>
        
        <form action="{{ route('company.employees.store', $company->id) }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Employee Name:</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter employee name" required>
            </div>

            <div class="form-group">
                <label for="email">Employee Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter employee email" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>

            <div class="form-group">
                <label for="department">Department:</label>
                <select class="form-control" id="department" name="department_id" required>
                    <option value="">Select Department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="designation">Designation:</label>
                <select class="form-control" id="designation" name="designation_id" required>
                    <option value="">Select Designation</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}">{{ $designation->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" class="form-control" id="dob" name="dob">
            </div>

            <div class="form-group">
                <label for="gender">Gender:</label>
                <select class="form-control" id="gender" name="gender">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="emergency_contact">Emergency Contact:</label>
                <input type="text" class="form-control" id="emergency_contact" name="emergency_contact">
            </div>

            <div class="form-group">
                <label for="joining_date">Joining Date:</label>
                <input type="date" class="form-control" id="joining_date" name="joining_date">
            </div>

            <div class="form-group">
                <label for="employment_type">Employment Type:</label>
                <select class="form-control" id="employment_type" name="employment_type">
                    <option value="permanent">Permanent</option>
                    <option value="contract">Contract</option>
                    <option value="intern">Intern</option>
                </select>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter employee's address"></textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Create Employee</button>
            <a href="{{ route('company.employees.index', $company->id) }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
@endsection
