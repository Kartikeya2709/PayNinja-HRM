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

                        {{-- @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif --}}

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
                                {{-- <form action="{{ route('company.settings.update') }}" method="POST">
                                    @csrf
                                    @method('PUT') --}}

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Company Name</label>
                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror" id="name"
                                                    name="name" value="{{ old('name', $company->name) }}" readonly disabled>
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
                                                    name="email" value="{{ old('email', $company->email) }}" readonly disabled>
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
                                                    name="phone" value="{{ old('phone', $company->phone) }}" readonly disabled>
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
                                                    value="{{ old('website', $company->domain) }}" readonly disabled>
                                                @error('website')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror"
                                            id="address" name="address" rows="3" readonly disabled
                                            required>{{ old('address', $company->address) }}</textarea>
                                        @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- <div class="text-center">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Save Changes
                                        </button>
                                    </div> --}}
                                {{-- </form> --}}
                            </div>

                            {{-- 🔹 Employee ID Prefix Tab --}}
                            <div class="tab-pane fade" id="emp-id-prefix" role="tabpanel" aria-labelledby="emp-id-prefix-tab">
                                <h5>Employee ID Prefix Settings</h5>
                                <form id="empIdPrefixForm" method="POST"
                                    action="{{ route('company.settings.save-employee-id-prefix') }}">
                                    @csrf
                                    @method('POST')
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

                                    <!-- Radio buttons for prefix mode -->
                                    <div class="row mb-3">
                                        <label class="form-label fw-bold">Prefix Configuration</label>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="prefix_mode" id="same_for_all" value="same_for_all" {{ old('prefix_mode', 'same_for_all') === 'same_for_all' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="same_for_all">
                                                    Same prefix for all employment types
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="prefix_mode" id="type_specific" value="type_specific" {{ old('prefix_mode') === 'type_specific' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="type_specific">
                                                    Different prefix for each employment type
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Same prefix for all types -->
                                    <div id="same_prefix_section">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="prefix" class="form-label">Prefix</label>
                                                    <input type="text" class="form-control @error('prefix') is-invalid @enderror" id="prefix" name="prefix" value="{{ old('prefix') }}">
                                                    @error('prefix')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="padding" class="form-label">Number Padding</label>
                                                    <input type="number" class="form-control @error('padding') is-invalid @enderror" id="padding" name="padding"
                                                        min="1" max="6" value="{{ old('padding') }}">
                                                    @error('padding')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="start" class="form-label">Start From</label>
                                                    <input type="number" class="form-control @error('start') is-invalid @enderror" id="start" name="start"
                                                        min="1" value="{{ old('start') }}">
                                                    @error('start')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="preview" class="form-label">Preview</label>
                                                    <input type="text" class="form-control" id="preview" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Different prefix for each type -->
                                    <div id="type_specific_section" style="display: none;">
                                        @if($employmentTypes && $employmentTypes->count() > 0)
                                            @foreach($employmentTypes as $index => $type)
                                                <div class="employment-type-section mb-4 p-3 border rounded">
                                                    <h6 class="mb-3">{{ $type->name }}</h6>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label">Prefix</label>
                                                                <input type="text" class="form-control type-prefix @error('types.' . $type->id . '.prefix') is-invalid @enderror"
                                                                    data-type-id="{{ $type->id }}"
                                                                    name="types[{{ $type->id }}][prefix]"
                                                                    value="{{ old('types.' . $type->id . '.prefix') }}">
                                                                @error('types.' . $type->id . '.prefix')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label">Number Padding</label>
                                                                <input type="number" class="form-control type-padding @error('types.' . $type->id . '.padding') is-invalid @enderror"
                                                                    data-type-id="{{ $type->id }}"
                                                                    name="types[{{ $type->id }}][padding]"
                                                                    min="1" max="6"
                                                                    value="{{ old('types.' . $type->id . '.padding') }}">
                                                                @error('types.' . $type->id . '.padding')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label">Start From</label>
                                                                <input type="number" class="form-control type-start @error('types.' . $type->id . '.start') is-invalid @enderror"
                                                                    data-type-id="{{ $type->id }}"
                                                                    name="types[{{ $type->id }}][start]"
                                                                    min="1"
                                                                    value="{{ old('types.' . $type->id . '.start') }}">
                                                                @error('types.' . $type->id . '.start')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label">Preview</label>
                                                                <input type="text" class="form-control type-preview"
                                                                    data-type-id="{{ $type->id }}" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-info">
                                                No employment types found. Please create employment types first.
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary" id="savePrefix">Save
                                            Prefix</button>
                                        <div id="savedMessage" class="alert alert-info mt-3" style="display: none;">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Employee ID prefix settings have been saved and cannot be modified.
                                            These settings will be used for generating new employee IDs.
                                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Get data from server-side rendering
        const savedData = @json($prefixData ?? ['status' => 'empty', 'data' => null]);

        // Handle radio button changes
        $('input[name="prefix_mode"]').on('change', function() {
            const mode = $(this).val();
            if (mode === 'same_for_all') {
                $('#same_prefix_section').show();
                $('#type_specific_section').hide();
                updateFormForMode();
                // Switch to prefix tab when changing modes
                $('#emp-id-prefix-tab').tab('show');
            } else {
                $('#same_prefix_section').hide();
                $('#type_specific_section').show();
                updateFormForMode();
                // Switch to prefix tab when changing modes
                $('#emp-id-prefix-tab').tab('show');
            }
        });

        // Initialize form with existing data
        function initializeForm() {
            if (savedData.status === 'empty') {
                $('#same_for_all').prop('checked', true);
                $('#same_prefix_section').show();
                $('#type_specific_section').hide();
                enableForm(true);
                showSaveButton(true);
                // Initialize preview for empty form
                updatePreview();
                return;
            }

            // If settings are already saved, disable all form elements
            if (savedData.status === 'common') {
                $('#same_for_all').prop('checked', true).prop('disabled', true);
                $('#type_specific').prop('checked', false).prop('disabled', true);
                $('#same_prefix_section').show();
                $('#type_specific_section').hide();
                fillFormData(savedData.data);
                enableForm(false);
                showSaveButton(false);
                showSavedMessage(true);
            } else if (savedData.status === 'specific') {
                $('#type_specific').prop('checked', true).prop('disabled', true);
                $('#same_for_all').prop('checked', false).prop('disabled', true);
                $('#same_prefix_section').hide();
                $('#type_specific_section').show();

                // Fill data for each employment type
                const typeData = savedData.data;
                Object.keys(typeData).forEach(typeId => {
                    fillTypeSpecificData(typeId, typeData[typeId]);
                });

                enableForm(false);
                showSaveButton(false);
                showSavedMessage(true);
            }
        }

        // Update form based on selected mode
        function updateFormForMode() {
            const mode = $('input[name="prefix_mode"]:checked').val();
            if (mode === 'same_for_all') {
                if (savedData && savedData.status === 'common') {
                    fillFormData(savedData.data);
                    enableForm(false);
                } else {
                    clearForm();
                    enableForm(true);
                }
            } else {
                // For type-specific mode, check if all types have data
                if (savedData && savedData.status === 'specific') {
                    const typeData = savedData.data;
                    const allTypesConfigured = @json($employmentTypes ?? []).every(type => typeData[type.id]);

                    if (allTypesConfigured) {
                        enableForm(false);
                    } else {
                        enableForm(true);
                    }
                } else {
                    clearTypeSpecificForms();
                    enableForm(true);
                }
            }
        }

        function clearForm() {
            $('#prefix').val('');
            $('#padding').val('');
            $('#start').val('');
            updatePreview();
        }

        function clearTypeSpecificForms() {
            $('.type-prefix').val('');
            $('.type-padding').val('');
            $('.type-start').val('');
            $('.type-preview').val('');
        }

        function fillFormData(data) {
            $('#prefix').val(data.prefix);
            $('#padding').val(data.padding);
            $('#start').val(data.start);
            updatePreview();
        }

        function fillTypeSpecificData(typeId, data) {
            $(`.type-prefix[data-type-id="${typeId}"]`).val(data.prefix);
            $(`.type-padding[data-type-id="${typeId}"]`).val(data.padding);
            $(`.type-start[data-type-id="${typeId}"]`).val(data.start);
            updateTypePreview(typeId);
        }

        function enableForm(enabled) {
            const mode = $('input[name="prefix_mode"]:checked').val();
            if (mode === 'same_for_all') {
                $('#prefix, #padding, #start').prop('disabled', !enabled);
            } else {
                $('.type-prefix, .type-padding, .type-start').prop('disabled', !enabled);
            }
        }

        function showSaveButton(show) {
            if (show) {
                $('#savePrefix').show();
            } else {
                $('#savePrefix').hide();
            }
        }

        function showSavedMessage(show) {
            if (show) {
                $('#savedMessage').show();
            } else {
                $('#savedMessage').hide();
            }
        }

        function updatePreview() {
            var prefix = $('#prefix').val() || '';
            var padding = parseInt($('#padding').val(), 10) || '';
            var start = parseInt($('#start').val(), 10) || '';

            var numberStr = start.toString();
            if (padding > numberStr.length) {
                numberStr = numberStr.padStart(padding, '0');
            }
            $('#preview').val(prefix + numberStr);
        }

        function updateTypePreview(typeId) {
            var prefix = $(`.type-prefix[data-type-id="${typeId}"]`).val() || '';
            var padding = parseInt($(`.type-padding[data-type-id="${typeId}"]`).val(), 10) || '';
            var start = parseInt($(`.type-start[data-type-id="${typeId}"]`).val(), 10) || '';

            var numberStr = start.toString();
            if (padding > numberStr.length) {
                numberStr = numberStr.padStart(padding, '0');
            }
            $(`.type-preview[data-type-id="${typeId}"]`).val(prefix + numberStr);
        }

        $('#prefix, #padding, #start').on('input', updatePreview);
        $('.type-prefix, .type-padding, .type-start').on('input', function() {
            const typeId = $(this).data('type-id');
            updateTypePreview(typeId);
        });

        // Initialize preview and form on page load
        updatePreview();
        initializeForm();

        // Handle form submission with SweetAlert confirmation
        $('#empIdPrefixForm').on('submit', function(e) {
            e.preventDefault();

            const mode = $('input[name="prefix_mode"]:checked').val();
            let confirmMessage = 'Are you sure? Once saved, these settings cannot be modified.';

            if (mode === 'same_for_all') {
                confirmMessage += '<br><br>This will apply the same prefix to all employment types.';
            } else {
                confirmMessage += '<br><br>This will set different prefixes for each employment type.';
            }

            Swal.fire({
                title: 'Confirm Save',
                html: confirmMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Save Settings',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable submit button to prevent double submission
                    $(this).find('button[type="submit"]').prop('disabled', true);
                    // Submit the form
                    this.submit();
                }
            });
        });

        // Handle successful form submission (redirect back)
        $(document).ready(function() {
            // Check if we have success message (meaning form was just submitted)
            if ($('.alert-success').length > 0) {
                // Re-initialize form to show disabled state
                const savedData = @json($prefixData ?? ['status' => 'empty', 'data' => null]);
                if (savedData.status !== 'empty') {
                    // Disable radio buttons and inputs
                    $('input[name="prefix_mode"]').prop('disabled', true);
                    $('#prefix, #padding, #start').prop('disabled', true);
                    $('.type-prefix, .type-padding, .type-start').prop('disabled', true);
                    $('#savePrefix').hide();
                    $('#savedMessage').show();
                }
            }

            // Check if we have validation errors and stay on prefix tab with old selected mode
            if ($('.alert-danger').length > 0 || $('.is-invalid').length > 0) {
                // Switch to prefix tab if there are validation errors
                $('#emp-id-prefix-tab').tab('show');

                // Restore the previously selected radio button state
                const oldPrefixMode = '{{ old("prefix_mode", "same_for_all") }}';
                $('input[name="prefix_mode"][value="' + oldPrefixMode + '"]').prop('checked', true);

                // Show the appropriate section based on old mode
                if (oldPrefixMode === 'same_for_all') {
                    $('#same_prefix_section').show();
                    $('#type_specific_section').hide();
                    // Update preview for same prefix mode with old values
                    updatePreview();
                } else {
                    $('#same_prefix_section').hide();
                    $('#type_specific_section').show();
                    // Update previews for type-specific mode with old values
                    @if($employmentTypes)
                        @foreach($employmentTypes as $type)
                            updateTypePreview({{ $type->id }});
                        @endforeach
                    @endif
                }
            }
        });
    });
</script>
@endpush