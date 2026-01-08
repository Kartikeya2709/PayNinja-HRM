@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Handbook</h4>
                    <a href="{{ route('handbooks.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('handbooks.update', Crypt::encrypt($handbook->id)) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 col-sm-12 form-group mb-4">
                                <label for="title">Title *</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $handbook->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-sm-12 form-group mb-4">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $handbook->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 form-group mb-4">
                                <label for="file">File (PDF, DOC, DOCX) - Leave empty to keep current file</label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf,.doc,.docx">
                                @if($handbook->file_path)
                                    <small class="form-text text-muted">Current file: {{ basename($handbook->file_path) }}</small>
                                @endif
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-sm-12 form-group mb-4">
                                <label for="version">Version</label>
                                <input type="text" class="form-control @error('version') is-invalid @enderror" id="version" name="version" value="{{ old('version', $handbook->version) }}">
                                @error('version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        </div>
                        <div class="form-group mb-4">
                            <label for="department_id">Department (Optional)</label>
                            <select class="form-control @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $handbook->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            @if($handbook->status === 'draft')
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="draft" {{ old('status', $handbook->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $handbook->status) === 'published' ? 'selected' : '' }}>Published</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @else
                                <p class="form-control-plaintext">
                                    <span class="badge badge-success">
                                        Published
                                    </span>
                                    <small class="text-muted">Status cannot be changed once published</small>
                                </p>
                            @endif
                        </div>
                        <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update Handbook</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
