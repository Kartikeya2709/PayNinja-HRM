@extends('layouts.app')

@section('title')
    {{ isset($holiday) ? 'Edit Holiday' : 'Create Holiday' }}
@endsection

@section('content')
    <div class="section-header">
        <h1>{{ isset($holiday) ? 'Edit Holiday' : 'Create Holiday' }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('company.academic-holidays.index') }}">Academic
                    Holidays</a></div>
            <div class="breadcrumb-item">{{ isset($holiday) ? 'Edit' : 'Create' }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form
                            action="{{ isset($holiday)
                                ? route('company.academic-holidays.update', $holiday->id)
                                : route('company.academic-holidays.store') }}"
                            method="POST">
                            @csrf
                            @if (isset($holiday))
                                @method('PUT')
                            @endif

                            <div class="form-group row mb-4">
                                <label for="name" class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Holiday
                                    Name <span class="text-danger">*</span></label>
                                <div class="col-sm-12 col-md-7">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $holiday->name ?? '') }}"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-4">
                                <label for="from_date" class="col-form-label text-md-right col-12 col-md-3 col-lg-3">From
                                    Date <span class="text-danger">*</span></label>
                                <div class="col-sm-12 col-md-7"> <input type="date"
                                        class="form-control @error('from_date') is-invalid @enderror" id="from_date"
                                        name="from_date"
                                        value="{{ old('from_date', isset($holiday->from_date) && is_object($holiday->from_date) ? $holiday->from_date->format('Y-m-d') : '') }}" />
                                    @error('from_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row mb-4">
                                <label for="to_date" class="col-form-label text-md-right col-12 col-md-3 col-lg-3">To Date
                                    <span class="text-danger">*</span></label>
                                <div class="col-sm-12 col-md-7"> <input type="date"
                                        class="form-control @error('to_date') is-invalid @enderror" id="to_date"
                                        name="to_date"
                                        value="{{ old('to_date', isset($holiday->to_date) && is_object($holiday->to_date) ? $holiday->to_date->format('Y-m-d') : '') }}" />
                                    @error('to_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="date-error" class="text-danger mt-2" style="display: none;">
                                        To Date cannot be earlier than From Date
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-4">
                                <label for="description"
                                    class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Description</label>
                                <div class="col-sm-12 col-md-7">
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3">{{ old('description', $holiday->description ?? '') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-4">
                                <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                                <div class="col-sm-12 col-md-7">
                                    <button type="submit"
                                        class="btn btn-primary">{{ isset($holiday) ? 'Update' : 'Create' }}
                                        Holiday</button>
                                    <a href="{{ route('company.academic-holidays.index') }}"
                                        class="btn btn-light ml-2">Cancel</a>
                                </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const fromDateInput = document.getElementById('from_date');
            const toDateInput = document.getElementById('to_date');
            const dateErrorDiv = document.getElementById('date-error');

            function validateDates() {
                if (fromDateInput.value && toDateInput.value && toDateInput.value < fromDateInput.value) {
                    dateErrorDiv.style.display = 'block';
                    return false;
                }
                dateErrorDiv.style.display = 'none';
                return true;
            }

            toDateInput.addEventListener('change', validateDates);
            fromDateInput.addEventListener('change', validateDates);

            // Add form submission validation
            document.querySelector('form').addEventListener('submit', function(e) {
                if (!validateDates()) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush
