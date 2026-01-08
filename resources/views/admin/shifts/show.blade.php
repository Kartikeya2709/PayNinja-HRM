@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Shift Details: {{ $shift->name }}</h1>
        <div>
            <a href="{{ route('admin.shifts.edit', Crypt::encrypt($shift->id)) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit fa-sm"></i> Edit
            </a>
            <a href="{{ route('admin.shifts.assign.show', Crypt::encrypt($shift->id)) }}" class="btn btn-info btn-sm">
                <i class="fas fa-user-plus fa-sm"></i> Assign to Employees
            </a>
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left fa-sm"></i> Back to Shifts
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Shift Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Company:</div>
                        <div class="col-md-8">{{ $shift->company->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Shift Name:</div>
                        <div class="col-md-8">{{ $shift->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Shift Hours:</div>
                        <div class="col-md-8">
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} -
                            {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                            @if($shift->is_night_shift)
                                <span class="badge badge-info ml-2">Night Shift</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Duration:</div>
                        <div class="col-md-8">{{ $shift->duration_in_hours }} hours</div>
                    </div>
                    @if($shift->has_break && $shift->break_start && $shift->break_end)
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Break Time:</div>
                        <div class="col-md-8">
                            {{ \Carbon\Carbon::parse($shift->break_start)->format('h:i A') }} -
                            {{ \Carbon\Carbon::parse($shift->break_end)->format('h:i A') }}
                            ({{ \Carbon\Carbon::parse($shift->break_start)->diffInMinutes(\Carbon\Carbon::parse($shift->break_end)) }} minutes)
                        </div>
                    </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Grace Period:</div>
                        <div class="col-md-8">{{ $shift->grace_period_minutes }} minutes</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Default Shift:</div>
                        <div class="col-md-8">
                            @if($shift->is_default)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </div>
                    </div>
                    @if($shift->description)
                    <div class="row mb-3">
                        <div class="col-md-4 font-weight-bold">Description:</div>
                        <div class="col-md-8">{{ $shift->description }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assigned Employees</h6>
                </div>
                <div class="card-body">
                    @if($shift->employeeShifts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Default</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shift->employeeShifts as $employeeShift)
                                        <tr>
                                            <td>{{ $employeeShift->employee->user->name }}</td>
                                            <td>{{ $employeeShift->start_date->format('M d, Y') }}</td>
                                            <td>{{ $employeeShift->end_date ? $employeeShift->end_date->format('M d, Y') : 'Ongoing' }}</td>
                                            <td>
                                                @if($employeeShift->is_default)
                                                    <span class="badge badge-success">Yes</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No employees assigned to this shift yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
