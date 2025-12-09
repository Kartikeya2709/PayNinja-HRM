@extends('layouts.app')

@section('title', 'Create Task')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-primary mb-0"><i class="fas fa-plus-circle"></i> Create New Task</h3>
                <a href="{{ route('tasks.index', ['assigned_to' => Auth::user()->employee->id ?? null]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Tasks</a>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Task Details</h5>
                </div>

                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <div class="card-body">
                        @include('company.tasks._form')
                    </div>
                    <div class="card-footer text-end bg-light">
                        <button class="btn btn-success btn-lg"><i class="fas fa-save"></i> Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

