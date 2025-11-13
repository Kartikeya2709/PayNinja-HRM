@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
<div class="section container">
     <div class="section-header">
            <h1>Create Department</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="#">center Department</a></div>
            </div>
        </div>

<div class="section-body">
    <div class="row">
        <div class="col-8 mx-auto">
            <div class="card">
                <div class="card-header justify-content-center mb-3">
                    <h3 class="card-title">Create New Department</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('company.departments.store', ['companyId' => Auth::user()->company_id]) }}" method="POST">
                        @csrf
                        <div class="form-group mb-4">
                            <label for="name">Name</label>
                            <input type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}" 
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
                                rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex gap-3 justify-content-center mt-4">
                           <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                           <i class="bi bi-save me-2"></i>Create Department
                           </button>
                           <a href="{{ route('company.departments.index', ['companyId' => Auth::user()->company_id]) }}" class="btn btn-danger px-4 rounded-pill">
                           <i class="bi bi-x-circle me-2"></i>Cancel
                           </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection
