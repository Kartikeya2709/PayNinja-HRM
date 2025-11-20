@extends('layouts.app')

@section('title', 'Create Team')

@section('content')
<div class="section container">
    <div class="section-header">
        <h1>Create Team</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="#">Create Team</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-8 mx-auto">
                <div class="card">
                    <div class="card-header justify-content-center">
                        <h3 class="card-title">Create New Team</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('company.teams.store', ['companyId' => Auth::user()->company_id]) }}"
                            method="POST">
                            @csrf
                            <div class="form-group mb-4">
                                <label for="name">Team Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="department_id">Department</label>
                                <select class="form-control @error('department_id') is-invalid @enderror"
                                    id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id') == $department->id ? 'selected' : '' }}>
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

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-group employee-select" style="display: none;">
                                <label for="reporter_id">Team Reporter</label>
                                <select class="form-control @error('reporter_id') is-invalid @enderror" id="reporter_id"
                                    name="reporter_id" required>
                                    <option value="">Select Reporter</option>
                                </select>
                                @error('reporter_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-group employee-select" style="display: none;">
                                <label for="reportee_ids">Team Members (Reportees)</label>
                                <select class="form-control @error('reportee_ids') is-invalid @enderror"
                                    id="reportee_ids" name="reportee_ids[]" multiple required>
                                </select>
                                @error('reportee_ids')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple members</small>
                            </div>

                            <div class="form-group mt-4 text-center">
                                <button type="submit" class="btn btn-primary">Create Team</button>
                                <a href="{{ route('company.teams.index', ['companyId' => Auth::user()->company_id]) }}"
                                    class="btn btn-danger">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#department_id').on('change', function() {
            var departmentId = $(this).val();
            if (departmentId) {
                $.ajax({
                    url: '{{ url("departments") }}/' + departmentId + '/employees',
                    type: 'GET',
                    success: function(data){
                        // Clear existing options
                        $('#reporter_id').html('<option value="">Select Reporter</option>');
                        $('#reportee_ids').empty();

                        // Add new options
                        data.forEach(function(employee) {
                            $('#reporter_id').append(new Option(
                                employee.name + ' - ' + employee.designation,
                                employee.id
                            ));
                            $('#reportee_ids').append(new Option(
                                employee.name + ' - ' + employee.designation,
                                employee.id
                            ));
                        });

                        // Show the employee select fields
                        $('.employee-select').show();
                    },
                    error: function() {
                        alert('Error fetching employees');
                    }
                });
            } else {
                // Hide and clear employee select fields if no department is selected
                $('.employee-select').hide();
                $('#reporter_id').html('<option value="">Select Reporter</option>');
                $('#reportee_ids').empty();
            }
        });

        // Initialize Select2 for multiple select
        $('#reportee_ids').select2({
            placeholder: 'Select Team Members',
            allowClear: true
        });
    });
    // Initialize select2 for better multiple select experience
    $(document).ready(function() {
        $('#reportee_ids').select2({
            placeholder: 'Select Team Members',
            allowClear: true
        });
    });
</script>
@endpush