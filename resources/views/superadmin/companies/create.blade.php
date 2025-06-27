@extends('layouts.app')

@section('title', 'Create Company')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('content')
    <div class="main-content-01">
        <section class="section">
            <div class="section-header">
                <h1>Create New Company</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('superadmin.companies.index') }}">Companies</a></div>
                    <div class="breadcrumb-item">Create Company</div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Create Company</h2>
                <p class="section-lead">
                    Fill in the form below to add a new company.
                </p>

                <form action="{{ route('superadmin.companies.store') }}" method="POST" enctype="multipart/form-data"
                    id="createCompanyForm">
                    @csrf

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Company Name <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-12 col-md-7">
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required
                                minlength="3" maxlength="255">
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Company Email <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-12 col-md-7">
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Domain</label>
                        <div class="col-sm-12 col-md-7">
                            <input type="text" class="form-control" name="domain" value="{{ old('domain') }}">
                            <small class="form-text text-muted">e.g., example.com</small>
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Phone</label>
                        <div class="col-sm-12 col-md-7">
                            <input type="text" class="form-control" maxlength="10" name="phone" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Address</label>
                        <div class="col-sm-12 col-md-7">
                            <textarea class="form-control" name="address" rows="3">{{ old('address') }}</textarea>
                        </div>
                    </div>

                    {{-- <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Select Company Admin <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-12 col-md-7">
                            <select name="admin_id" class="form-control" required>
                                <option value="" disabled selected>Select an Admin</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div> --}}

                    <div class="form-group row mb-4">
                        <div class="col-sm-12 col-md-7 offset-md-3">
                            <button type="submit" class="btn btn-primary">Create Company</button>
                            <a href="{{ route('superadmin.companies.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('createCompanyForm').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                alert('Please fill out all required fields correctly.');
            }
        });
    </script>
@endpush
