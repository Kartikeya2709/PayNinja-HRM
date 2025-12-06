@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Financial Year</h3>
                </div>

                <form method="POST" action="{{ route('company-admin.financial-years.store') }}">
                    @csrf

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
                                   value="{{ old('name', 'FY ' . date('Y') . '-' . (date('Y') + 1)) }}"
                                   placeholder="e.g., FY 2024-2025" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                           value="{{ old('start_date') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                           value="{{ old('end_date') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                       value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Set as Active Financial Year
                                </label>
                                <small class="form-text text-muted">
                                    If checked, this will become the active financial year for all disbursement cycles.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Financial Year
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
    // Set default dates if not provided
    $(document).ready(function() {
        const currentYear = new Date().getFullYear();
        const startDate = $('#start_date').val() || `${currentYear}-04-01`; // Default to April 1st
        const endDate = $('#end_date').val() || `${currentYear + 1}-03-31`; // Default to March 31st next year

        $('#start_date').val(startDate);
        $('#end_date').val(endDate);

        // Update name field based on dates
        function updateNameField() {
            const startYear = new Date($('#start_date').val()).getFullYear();
            const endYear = new Date($('#end_date').val()).getFullYear();
            $('#name').val(`FY ${startYear}-${endYear}`);
        }

        $('#start_date, #end_date').on('change', updateNameField);
    });
</script>
@endpush
