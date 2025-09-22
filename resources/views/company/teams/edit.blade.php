@extends('layouts.app')

@section('title', 'Edit Team')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Edit Team</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Edit Team</a></div>
            </div>
        </div>

<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Team</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('company.teams.update', ['companyId' => Auth::user()->company_id, 'team' => $team]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-4">
                            <label for="name">Team Name</label>
                            <input type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name', $team->name) }}" 
                                required>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="department_id">Department</label>
                            <select class="form-control @error('department_id') is-invalid @enderror" 
                                id="department_id" 
                                name="department_id" 
                                required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $team->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="description">Description</label>
                            <textarea 
                                class="form-control @error('description') is-invalid @enderror" 
                                id="description" 
                                name="description" 
                                rows="3">{{ old('description', $team->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="reporter_id">Team Reporter</label>
                            <select class="form-control @error('reporter_id') is-invalid @enderror" 
                                id="reporter_id" 
                                name="reporter_id" 
                                required>
                                <option value="">Select Reporter</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ in_array($employee->id, $reporters) ? 'selected' : '' }}>
                                        {{ $employee->name }} - {{ $employee->designation->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('reporter_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="reportee_ids">Team Members (Reportees)</label>
                            <select class="form-control @error('reportee_ids') is-invalid @enderror" 
                                id="reportee_ids" 
                                name="reportee_ids[]" 
                                multiple 
                                required>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ in_array($employee->id, $reportees) ? 'selected' : '' }}>
                                        {{ $employee->name }} - {{ $employee->designation->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('reportee_ids')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple members</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Team</button>
                            <a href="{{ route('company.teams.index', ['companyId' => Auth::user()->company_id]) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize select2 for better multiple select experience
    $(document).ready(function() {
        $('#reportee_ids').select2({
            placeholder: 'Select Team Members',
            allowClear: true
        });
    });
</script>
@endpush
