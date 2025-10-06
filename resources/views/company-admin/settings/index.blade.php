@extends('layouts.app')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Company Settings</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="#">Company Settings</a></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card glass-box">
                    <div class="card-header">
                        <h5 class="mb-0">Company Settings</h5>
                    </div>

                    <div class="card-body mt-4">
                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        {{-- 🔹 Modern Glass Tabs --}}
                        <ul class="nav nav-pills custom-tabs" id="companySettingsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="company-info-tab" data-bs-toggle="tab"
                                    data-bs-target="#company-info" type="button" role="tab" aria-controls="company-info"
                                    aria-selected="true">
                                    <i class="fas fa-building me-2"></i> Company Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="emp-id-prefix-tab" data-bs-toggle="tab"
                                    data-bs-target="#emp-id-prefix" type="button" role="tab"
                                    aria-controls="emp-id-prefix" aria-selected="false">
                                    <i class="fas fa-id-badge me-2"></i> Employee ID Prefix
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content p-4" id="companySettingsTabContent">

                            {{-- 🔹 Company Info Tab --}}
                            <div class="tab-pane fade show active" id="company-info" role="tabpanel"
                                aria-labelledby="company-info-tab">
                                <form action="{{ route('company-admin.settings.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Company Name</label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror" id="name"
                                                    name="name" value="{{ old('name', $company->name) }}" required>
                                                @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Company Email</label>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" id="email"
                                                    name="email" value="{{ old('email', $company->email) }}" required>
                                                @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="text"
                                                    class="form-control @error('phone') is-invalid @enderror" id="phone"
                                                    name="phone" value="{{ old('phone', $company->phone) }}" required>
                                                @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="website" class="form-label">Website</label>
                                                <input type="url"
                                                    class="form-control @error('website') is-invalid @enderror"
                                                    id="website" name="website"
                                                    value="{{ old('website', $company->domain) }}">
                                                @error('website')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror"
                                            id="address" name="address" rows="3"
                                            required>{{ old('address', $company->address) }}</textarea>
                                        @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- 🔹 Employee ID Prefix Tab --}}
                            <div class="tab-pane fade" id="emp-id-prefix" role="tabpanel"
                                aria-labelledby="emp-id-prefix-tab">
                                <h5>Employee ID Prefix Settings</h5>
                                <form id="empIdPrefixForm" method="POST"
                                    action="{{ route('company-admin.settings.save-employee-id-prefix') }}">
                                    @csrf
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="employee-id-clr">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Employee ID settings can be common for all employment types or specific
                                                to each type.
                                                Once saved, the settings cannot be modified, except for adding settings
                                                for an unused employment type.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Employment Type</label>
                                                <select class="form-select" name="employment_type" id="employment_type">
                                                    <option value="">Common for All</option>
                                                    <option value="permanent">Permanent Employee</option>
                                                    <option value="trainee">Trainee</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="prefix" class="form-label">Prefix</label>
                                                <input type="text" class="form-control" id="prefix" name="prefix"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="padding" class="form-label">Number Padding</label>
                                                <input type="number" class="form-control" id="padding" name="padding"
                                                    min="1" max="6" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="start" class="form-label">Start From</label>
                                                <input type="number" class="form-control" id="start" name="start"
                                                    min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="preview" class="form-label">Preview</label>
                                                <input type="text" class="form-control" id="preview" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary" id="savePrefix">Save
                                            Prefix</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div> {{-- tab-content --}}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    /* 🔹 Custom glass-style tabs */
    .custom-tabs .nav-link {
        border-radius: 12px;
        margin-right: 8px;
        padding: 10px 18px;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.12);
        color: #222;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .custom-tabs .nav-link:hover {
        background: rgba(37, 99, 235, 0.12);
        transform: translateY(-2px);
        color: #2563eb;
    }

    .custom-tabs .nav-link.active {
        background: linear-gradient(135deg, #2563eb, #1e40af);
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* 🔹 Glass effect for tab content */
    .glass-box {
        border-radius: 16px;
        backdrop-filter: blur(12px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let savedData = null;

        // Load existing prefix data
        function loadPrefixData() {
            $.get("{{ route('company-admin.settings.get-employee-id-prefix') }}", function(response) {
                savedData = response;
                
                if (response.status === 'empty') {
                    enableForm(true);
                    return;
                }

                if (response.status === 'common') {
                    $('#employment_type').val('');
                    fillFormData(response.data);
                    enableForm(false);
                } else if (response.status === 'specific') {
                    if (response.data.permanent && response.data.trainee) {
                        $('#employment_type').val('permanent');
                        fillFormData(response.data.permanent);
                        enableForm(false);
                    } else {
                        const savedType = response.data.permanent ? 'permanent' : 'trainee';
                        const unsavedType = response.data.permanent ? 'trainee' : 'permanent';
                        
                        $('#employment_type').val(unsavedType);
                        $('#employment_type option').not(`[value='${unsavedType}']`).not('[value=""]').prop('disabled', true);
                        enableForm(true);
                    }
                }

                updateFormBasedOnSavedData();
            });
        }

        // Handle employment type change
        $('#employment_type').on('change', function() {
            updateFormBasedOnSavedData();
        });

        // Update form based on selected employment type and saved data
        function updateFormBasedOnSavedData() {
            if (!savedData) return;

            const selectedType = $('#employment_type').val();

            if (savedData.status === 'common') {
                // if (selectedType) {
                    fillFormData(savedData.data);
                    enableForm(false);
                // } else {
                //     clearForm();
                //     enableForm(false);
                // }
            } else if (savedData.status === 'specific') {
                if (!selectedType) {
                    clearForm();
                    enableForm(false);
                } else {
                    const typeData = savedData.data[selectedType];
                    if (typeData) {
                        fillFormData(typeData);
                        enableForm(false);
                    } else {
                        clearForm();
                        enableForm(true);
                    }
                }
            }
        }

        function clearForm() {
            $('#prefix').val('');
            $('#padding').val('');
            $('#start').val('');
            updatePreview();
        }

        function fillFormData(data) {
            $('#prefix').val(data.prefix);
            $('#padding').val(data.padding);
            $('#start').val(data.start);
            updatePreview();
        }

        function enableForm(enabled) {
            $('#prefix, #padding, #start, #savePrefix').prop('disabled', !enabled);
        }

        function updatePreview() {
            var prefix = $('#prefix').val() || '';
            var padding = parseInt($('#padding').val(), 10) || 0;
            var start = parseInt($('#start').val(), 10) || 0;

            var numberStr = start.toString();
            if (padding > numberStr.length) {
                numberStr = numberStr.padStart(padding, '0');
            }
            $('#preview').val(prefix + numberStr);
        }

        $('#prefix, #padding, #start').on('input', updatePreview);
        
        // Initialize preview and load data on page load
        updatePreview();
        loadPrefixData();

        // Handle form submission
        $('#empIdPrefixForm').on('submit', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure? Once saved, these settings cannot be modified.')) {
                return;
            }

            $(this).find('button[type="submit"]').prop('disabled', true);
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success('Employee ID prefix settings saved successfully');
                        loadPrefixData();
                    } else {
                        toastr.error('Failed to save settings');
                    }
                },
                error: function() {
                    toastr.error('An error occurred while saving settings');
                },
                complete: function() {
                    $('#empIdPrefixForm button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush