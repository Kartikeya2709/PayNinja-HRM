@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Employees in {{ $company->name }}</h2>
<<<<<<< Updated upstream
        {{-- <a href="{{ route('company.employees.create', $company->id) }}" class="btn btn-primary">Create New Employee</a> --}}
        <a href="{{ route('company-admin.employees.create') }}" class="btn btn-primary">Create New Employee</a>
=======
        <a href="{{ route('company.employees.create', $company->id) }}" class="btn button">Create New Employee</a>
>>>>>>> Stashed changes

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
                            <a href="{{ route('company.employees.edit', [$company->id, $employee->id]) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('company.employees.destroy', [$company->id, $employee->id]) }}" method="POST" style="display:inline;">
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
