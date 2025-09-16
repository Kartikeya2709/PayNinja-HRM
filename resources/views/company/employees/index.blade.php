@extends('layouts.app')

@section('content')
    <div class="container">
    <section class="section">
            <div class="section-header">
                <h1>Employees</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="">Employees</div>
                </div>
            </div>
             <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Employees in {{ $company->name }}</h5>
        {{-- <a href="{{ route('company.employees.create', $company->id) }}" class="btn btn-primary">Create New Employee</a> --}}
        <a href="{{ route('company-admin.employees.create') }}" class="btn button">Create New Employee</a>
</div>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->user ? $employee->user->email : $employee->email }}</td>
                        <td>{{ $employee->department ? $employee->department->name : 'N/A' }}</td>
                        <td>
                            <a href="{{ route('company.employees.edit', $employee->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('company.employees.destroy', $employee->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
