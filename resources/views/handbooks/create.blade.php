@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Handbook</h4>
                    <a href="{{ route('handbooks.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('handbooks.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4 form-group">
                                    <label for="title">Title *</label>
                                       <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                                         @error('title')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                         @enderror
                            </div>
                        
                            <div class="col-md-6 col-sm-12 mb-4 form-group">
                                    <label for="description">Description</label>
                                       <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                       @error('description')
                                       <div class="invalid-feedback">{{ $message }}</div>
                                       @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4 form-group">
                                    <label for="file">File (PDF, DOC, DOCX) *</label>
                                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf,.doc,.docx" >
                                    @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4 form-group">
                                    <label for="version">Version</label>
                                    <input type="text" class="form-control @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', 'v1.0') }}">
                                    @error('version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4 form-group">
                                    <label for="department_id">Department (Optional)</label>
                                    <select class="form-control @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                    @endforeach
                                    </select>
                                   @error('department_id')
                                  <div class="invalid-feedback">{{ $message }}</div>
                                  @enderror
                            </div>
                        
                            <div class="col-md-6 col-sm-12 mb-4 form-group">
                                 <label for="status">Status *</label>
                                 <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                 <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                 <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                 </select>
                                 @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary">Create Handbook</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection