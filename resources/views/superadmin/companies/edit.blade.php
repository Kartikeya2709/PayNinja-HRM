@extends('layouts.app')

@section('title', 'Edit Company')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('content')
    <div class="main-content">
        <section class="section container">
            <div class="section-header">
                <h1>Edit Company</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('superadmin.companies.index') }}">Companies</a></div>
                    <div class="breadcrumb-item">Edit Company</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Edit Company: {{ $company->name }}</h2>
                <p class="section-lead mt-2">
                    Update the company details below.
                </p>

                <form action="{{ route('superadmin.companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Company Name <span class="text-danger">*</span></label>
                        <div class="col-sm-12 col-md-9">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $company->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Company Email <span class="text-danger">*</span></label>
                        <div class="col-sm-12 col-md-9">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $company->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Domain</label>
                        <div class="col-sm-12 col-md-9">
                            <input type="text" class="form-control @error('domain') is-invalid @enderror" name="domain" value="{{ old('domain', $company->domain) }}">
                            <small class="form-text text-muted">e.g., example.com</small>
                            @error('domain')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Phone</label>
                        <div class="col-sm-12 col-md-9">
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" maxlength="10" value="{{ old('phone', $company->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Address</label>
                        <div class="col-sm-12 col-md-9">
                            <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address', $company->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    {{-- <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Current Logo</label>
                        <div class="col-sm-12 col-md-9">
                            @if ($company->logo)
                                <img src="{{ asset('storage/' . $company->logo) }}" alt="Company Logo" class="img-thumbnail mb-2" style="max-height: 100px;">
                            @else
                                <p>No logo uploaded.</p>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">New Logo (Optional)</label>
                        <div class="col-sm-12 col-md-9">
                            <div id="image-preview" class="image-preview">
                                <label for="image-upload" id="image-label">Choose File</label>
                                <input type="file" name="logo" id="image-upload" class="@error('logo') is-invalid @enderror" />
                            </div>
                            <small class="form-text text-muted">Leave blank if you don't want to change the logo.</small>
                            @error('logo')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div> --}}

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                        <div class="col-sm-12 col-md-9">
                            <button type="submit" class="btn btn-primary">Update Company</button>
                            <a href="{{ route('superadmin.companies.index') }}" class="btn btn-danger">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('library/jquery.uploadpreview/jquery.uploadPreview.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label",
                label_default: "Choose File",
                label_selected: "Change File",
                no_label: false,
                success_callback: null
            });
        });
    </script>
@endpush
