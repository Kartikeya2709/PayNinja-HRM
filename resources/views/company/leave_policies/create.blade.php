@extends('layouts.app')

@section('title', isset($leavePolicy) ? 'Edit Leave Policy' : 'Create Leave Policy')

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>{{ isset($leavePolicy) ? 'Edit Leave Policy' : 'Create Leave Policy' }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('company.leave-policies.index') }}">Leave Policies</a></div>
            <div class="breadcrumb-item active"><a href="">{{ isset($leavePolicy) ? 'Edit' : 'Create' }}</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ isset($leavePolicy) ? route('company.leave-policies.update', $leavePolicy->id) : route('company.leave-policies.store') }}" method="POST">
                            @csrf
                            @if(isset($leavePolicy))
                                @method('PUT')
                            @endif

                            <div class="form-group mb-4">
                                <label for="financial_year_id">Financial Year <span class="text-danger">*</span></label>
                                <select name="financial_year_id"
                                        id="financial_year_id"
                                        class="form-control @error('financial_year_id') is-invalid @enderror"
                                        required>
                                    <option value="">Select Financial Year</option>
                                    @foreach($financialYears as $year)
                                        <option value="{{ $year->id }}"
                                            {{ (isset($leavePolicy) && $leavePolicy->financial_year_id == $year->id) || old('financial_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }} ({{ $year->start_date->format('d-m-Y') }} to {{ $year->end_date->format('d-m-Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('financial_year_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="name">Policy Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ isset($leavePolicy) ? $leavePolicy->name : old('name') }}"
                                       placeholder="e.g., Standard Leave Policy"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="description">Description</label>
                                <textarea name="description"
                                          id="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Policy description and details">{{ isset($leavePolicy) ? $leavePolicy->description : old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="is_active"
                                           name="is_active"
                                           value="1"
                                           {{ (isset($leavePolicy) && $leavePolicy->is_active) || old('is_active') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">
                                        Active Policy
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($leavePolicy) ? 'Update Policy' : 'Create Policy' }}
                                </button>
                                <a href="{{ route('company.leave-policies.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
