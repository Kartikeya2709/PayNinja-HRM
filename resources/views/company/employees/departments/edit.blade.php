@extends('layouts.app')

@section('title', 'Edit Department')

@section('content')
    <div class="section container">
        <div class="section-header">
            <h1>Edit Department</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="#">Edit Department</a></div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-6 mx-auto">
                    <div class="card">
                        <div class="card-header justify-content-center">
                            <h3 class="card-title">Edit Department</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('company.departments.update', ['companyId' => Auth::user()->company_id, 'department' => $department]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group mb-4">
                                    <label for="name">Name</label>
                                    <input type="text" 
                                        class="form-control @error('name') is-invalid @enderror" 
                                        id="name" 
                                        name="name" 
                                        value="{{ old('name', $department->name) }}" 
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea 
                                        class="form-control @error('description') is-invalid @enderror" 
                                        id="description" 
                                        name="description" 
                                        rows="3">{{ old('description', $department->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 text-center">
                                    <button type="submit" class="btn btn-primary">Update Department</button>
                                    <a href="{{ route('company.departments.index', ['companyId' => Auth::user()->company_id]) }}" class="btn btn-danger">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
