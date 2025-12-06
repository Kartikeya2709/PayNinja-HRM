@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Financial Year</h3>
                </div>

                <form method="POST" action="{{ route('company-admin.financial-years.update', $financialYear->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="name">Financial Year Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="{{ old('name', $financialYear->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                           value="{{ old('start_date', $financialYear->start_date->format('Y-m-d')) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                           value="{{ old('end_date', $financialYear->end_date->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', $financialYear->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Set as Active Financial Year
                                </label>
                                <small class="form-text text-muted">
                                    If checked, this will become the active financial year for all disbursement cycles.
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_locked" name="is_locked"
                                       value="1" {{ old('is_locked', $financialYear->is_locked) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_locked">
                                    Lock Financial Year
                                </label>
                                <small class="form-text text-muted">
                                    Locked financial years cannot be modified or deleted.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Financial Year
                        </button>
                        <a href="{{ route('company-admin.financial-years.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update name field based on dates
    function updateNameField() {
        const startYear = new Date($('#start_date').val()).getFullYear();
        const endYear = new Date($('#end_date').val()).getFullYear();
        $('#name').val(`FY ${startYear}-${endYear}`);
    }

    $('#start_date, #end_date').on('change', updateNameField);
</script>
@endpush
