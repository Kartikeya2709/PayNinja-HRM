@extends('layouts.app')

@section('title', isset($holiday) ? 'Edit Holiday' : 'Create Holiday')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($holiday) ? 'Edit Holiday' : 'Create Holiday' }}</h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ isset($holiday) 
                        ? route('company.academic-holidays.update', $holiday->id) 
                        : route('company.academic-holidays.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($holiday))
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="name">Holiday Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $holiday->name ?? '') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="from_date">From Date</label>
                            <input type="date" 
                                   class="form-control @error('from_date') is-invalid @enderror" 
                                   id="from_date" 
                                   name="from_date" 
                                   value="{{ old('from_date', isset($holiday) ? $holiday->from_date->format('Y-m-d') : '') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="to_date">To Date</label>
                            <input type="date" 
                                   class="form-control @error('to_date') is-invalid @enderror" 
                                   id="to_date" 
                                   name="to_date" 
                                   value="{{ old('to_date', isset($holiday) ? $holiday->to_date->format('Y-m-d') : '') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $holiday->description ?? '') }}</textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($holiday) ? 'Update' : 'Create' }} Holiday
                            </button>
                            <a href="{{ route('company.academic-holidays.index') }}" 
                               class="btn btn-secondary">Cancel</a>
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
    // Validate that to_date is not before from_date
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');

    toDateInput.addEventListener('change', function() {
        if (fromDateInput.value && this.value && this.value < fromDateInput.value) {
            alert('To Date cannot be earlier than From Date');
            this.value = fromDateInput.value;
        }
    });

    fromDateInput.addEventListener('change', function() {
        if (toDateInput.value && this.value && toDateInput.value < this.value) {
            toDateInput.value = this.value;
        }
    });
});
</script>
@endpush
