<!-- Geolocation Settings Section -->
<div class="card mb-4 px-0 py-0">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Geolocation Exemptions</h6>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-label">Exempt Departments from Geolocation Check</label>
                    <select class="form-control select2" name="exempted_departments[]" multiple>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" 
                                {{ in_array($department->id, $settings->exemptedDepartments->pluck('id')->toArray()) ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select departments that don't require geolocation for attendance</small>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-label">Exempt Individual Employees from Geolocation Check</label>
                    <select class="form-control select2" name="exempted_employees[]" multiple>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ in_array($employee->id, $settings->exemptedEmployees->pluck('id')->toArray()) ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select specific employees that don't require geolocation for attendance</small>
                </div>
            </div>
        </div>
    </div>
</div>