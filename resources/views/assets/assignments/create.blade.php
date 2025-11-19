@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-center">
                    <h3 class="card-title">Assign Asset</h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('assets.assignments.store') }}" method="POST" class="mt-3">
                        @csrf

                        <div class="form-group">
                            <label for="asset_id">Asset <span class="text-danger">*</span></label>
                            <select class="form-control @error('asset_id') is-invalid @enderror" 
                                    id="asset_id" name="asset_id" required>
                                <option value="">Select Asset</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id || request('asset') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->name }} ({{ $asset->asset_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- <div class="form-group mt-3">
                            <label for="employee_id">Employee <span class="text-danger">*</span></label>
                            <select class="form-control @error('employee_id') is-invalid @enderror" 
                                    id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $id => $name)
                                    <option value="{{ $id }}" {{ old('employee_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                          <div class="form-group mt-3">
                            <label for="employee_id">Employee <span class="text-danger">*</span></label>
                            <select class="form-control @error('employee_id') is-invalid @enderror" 
                                    id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $id => $name)
                                    <option value="{{ $id }}" {{ old('employee_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div> -->
                
                               <div class="row mt-3">
  

    <!-- Department -->
    <div class="col-md-4 form-group">
        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                <select id="department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
        @error('department_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <!-- Designation -->
    <div class="col-md-4 form-group">
        <label for="designation_id" class="form-label">Designation <span class="text-danger">*</span></label>
        <select id="designation_id" name="designation_id" class="form-control @error('designation_id') is-invalid @enderror" required>
            <option value="">Select Designation</option>
        </select>
        @error('designation_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <!-- Employee -->
    <div class="col-md-4 form-group">
        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
       <select id="employee_id" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
            <option value="">Select Employee</option>
        </select>
        @error('employee_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>
</div>




            
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assigned_date">Assignment Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('assigned_date') is-invalid @enderror" 
                                           id="assigned_date" name="assigned_date" value="{{ old('assigned_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expected_return_date">Expected Return Date</label>
                                    <input type="date" class="form-control @error('expected_return_date') is-invalid @enderror" 
                                           id="expected_return_date" name="expected_return_date" value="{{ old('expected_return_date') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="condition_on_assignment">Condition on Assignment <span class="text-danger">*</span></label>
                            <select class="form-control @error('condition_on_assignment') is-invalid @enderror"
                                    id="condition_on_assignment" name="condition_on_assignment" required>
                                <option value="">Select Condition</option>
                                <option value="good" {{ old('condition_on_assignment') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="fair" {{ old('condition_on_assignment') == 'fair' ? 'selected' : '' }}>Fair</option>
                                <option value="poor" {{ old('condition_on_assignment') == 'poor' ? 'selected' : '' }}>Poor</option>
                                <option value="damaged" {{ old('condition_on_assignment') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex gap-3 justify-content-center mt-4">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                           <i class="bi bi-link-45deg me-2"></i>Assign Asset
                        </button>
                        <a href="{{ route('assets.index') }}" class="btn btn-danger px-4 rounded-pill">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                       </a>
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
    const departments = @json($departments);
    const oldDepartmentId = '{{ old('department_id') }}';
    const oldDesignationId = '{{ old('designation_id') }}';
    const oldEmployeeId = '{{ old('employee_id') }}';

    const deptSelect = document.getElementById('department_id');
    const designationSelect = document.getElementById('designation_id');
    const employeeSelect = document.getElementById('employee_id');

    function populateDesignations(deptId, selectedDesId = null) {
        designationSelect.innerHTML = '<option value="">Select Designation</option>';
        if(!deptId) return;

        const department = departments.find(d => d.id == deptId);
        if(department && department.designations) {
            department.designations.forEach(des => {
                const option = document.createElement('option');
                option.value = des.id;
                option.text = des.title;
                if (selectedDesId && des.id == selectedDesId) {
                    option.selected = true;
                }
                designationSelect.appendChild(option);
            });
        }
    }

    function populateEmployees(deptId, desId, selectedEmpId = null) {
        employeeSelect.innerHTML = '<option value="">Select Employee</option>';
        if(!deptId || !desId) return;

        const department = departments.find(d => d.id == deptId);
        const designation = department.designations.find(des => des.id == desId);
        if(designation && designation.employees) {
            designation.employees.forEach(emp => {
                const option = document.createElement('option');
                option.value = emp.id;
                option.text = emp.name;
                if (selectedEmpId && emp.id == selectedEmpId) {
                    option.selected = true;
                }
                employeeSelect.appendChild(option);
            });
        }
    }

    deptSelect.addEventListener('change', function() {
        const deptId = this.value;
        populateDesignations(deptId);
        employeeSelect.innerHTML = '<option value="">Select Employee</option>';
    });

    designationSelect.addEventListener('change', function() {
        const desId = this.value;
        const deptId = deptSelect.value;
        populateEmployees(deptId, desId);
    });

    // Handle old values on page load
    if (oldDepartmentId) {
        deptSelect.value = oldDepartmentId;
        populateDesignations(oldDepartmentId, oldDesignationId);
        if (oldDesignationId) {
            populateEmployees(oldDepartmentId, oldDesignationId, oldEmployeeId);
        }
    }
</script>
@endpush

