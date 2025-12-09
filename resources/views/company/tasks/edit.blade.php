@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary mb-0"><i class="fas fa-edit"></i> Edit Task</h3>
                <a href="{{ route('tasks.index', ['assigned_to' => Auth::user()->employee->id ?? null]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Tasks</a>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Task Details</h5>
                </div>

                <form method="POST" action="{{ route('tasks.update', $task->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @include('company.tasks._form')
                    </div>
                    <div class="card-footer text-end bg-light">
                        <button class="btn btn-warning btn-lg"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
