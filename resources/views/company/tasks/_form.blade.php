<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="mb-4">
            <label for="title" class="form-label fw-bold"><i class="fas fa-heading text-primary"></i> Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control form-control-lg" value="{{ old('title', $task->title ?? '') }}" required placeholder="Enter task title">
        </div>

        <div class="mb-4">
            <label for="description" class="form-label fw-bold"><i class="fas fa-align-left text-primary"></i> Description</label>
            <textarea name="description" id="description" rows="5" class="form-control" placeholder="Describe the task details">{{ old('description', $task->description ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <div class="d-flex gap-3 align-items-center">
                <label class="form-label fw-bold mb-0"><i class="fas fa-check-circle text-primary"></i> Applicability</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="assign_mode" id="mode_applicability" value="applicability" {{ old('assign_mode', isset($task) && $task->designations && $task->designations->count() ? 'applicability' : 'applicability') == 'applicability' ? 'checked' : '' }}>
                        <label class="form-check-label" for="mode_applicability">Set Applicability</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="assign_mode" id="mode_direct" value="direct" {{ old('assign_mode') == 'direct' ? 'checked' : '' }}>
                        <label class="form-check-label" for="mode_direct">Send To Employees</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">Choose whether to assign by applicability (designations/departments) or directly select employees.</small>
        </div>

        <div class="row g-3">
            <div class="col-md-4" id="col_assigned_to">
                <label for="assigned_to" class="form-label fw-bold"><i class="fas fa-users text-primary"></i> Assign To</label>
                <select name="assigned_to[]" id="assigned_to" class="form-select select2" multiple style="width: 100%;">
                    <option value="self">My Self</option>
                    @foreach($employees as $emp)
                        @php
                            $selected = false;
                            $old = old('assigned_to');
                            if (is_array($old)) {
                                $selected = in_array($emp->id, $old);
                            } elseif (isset($task) && $task->assignedToMany) {
                                $selected = in_array($emp->id, $task->assignedToMany->pluck('id')->toArray());
                            }
                        @endphp
                        <option value="{{ $emp->id }}" {{ $selected ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Type to search employees or select "My Self"</small>
            </div>
            <div class="col-md-4">
                <label for="designations" class="form-label fw-bold"><i class="fas fa-id-badge text-primary"></i> Designations</label>
                <select name="designations[]" id="designations" class="form-select select2" multiple style="width:100%;">
                    @foreach($designations ?? [] as $d)
                        @php
                            $selected = false;
                            $old = old('designations');
                            if (is_array($old)) {
                                $selected = in_array($d->id, $old);
                            } elseif (isset($task) && $task->designations) {
                                $selected = in_array($d->id, $task->designations->pluck('id')->toArray());
                            }
                        @endphp
                        <option value="{{ $d->id }}" {{ $selected ? 'selected' : '' }}>{{ $d->title }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Assign by designation (select multiple)</small>
            </div>
            <div class="col-md-4">
                <label for="departments" class="form-label fw-bold"><i class="fas fa-building text-primary"></i> Departments</label>
                <select name="departments[]" id="departments" class="form-select select2" multiple style="width:100%;">
                    @foreach($departments ?? [] as $dept)
                        @php
                            $selected = false;
                            $old = old('departments');
                            if (is_array($old)) {
                                $selected = in_array($dept->id, $old);
                            } elseif (isset($task) && $task->departments) {
                                $selected = in_array($dept->id, $task->departments->pluck('id')->toArray());
                            }
                        @endphp
                        <option value="{{ $dept->id }}" {{ $selected ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Assign by department (select multiple)</small>
            </div>
            <div class="col-md-3" id="col_team_lead">
                <label for="team_lead_id" class="form-label fw-bold"><i class="fas fa-user-tie text-primary"></i> Team Lead</label>
                <select name="team_lead_id" id="team_lead_id" class="form-select select2" style="width:100%;">
                    <option value="">-- Select Team Lead --</option>
                    @foreach($employees ?? [] as $emp)
                        @php
                            $selected = isset($task) && $task->team_lead_id && $task->team_lead_id === $emp->id;
                        @endphp
                        <option value="{{ $emp->id }}" {{ $selected ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Team lead can update task status</small>
            </div>
            <div class="col-md-3">
                <label for="exemptions" class="form-label fw-bold"><i class="fas fa-user-slash text-danger"></i> Exempt Employees</label>
                <select name="exemptions[]" id="exemptions" class="form-select select2" multiple style="width:100%;">
                    @foreach($employees ?? [] as $emp)
                        @php
                            $selected = false;
                            $old = old('exemptions');
                            if (is_array($old)) {
                                $selected = in_array($emp->id, $old);
                            } elseif (isset($task) && $task->exemptions) {
                                $selected = in_array($emp->id, $task->exemptions->pluck('id')->toArray());
                            }
                        @endphp
                        <option value="{{ $emp->id }}" {{ $selected ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Exclude employees from assignment</small>
            </div>
            <div class="col-md-2">
                <label for="priority" class="form-label fw-bold"><i class="fas fa-exclamation-triangle text-warning"></i> Priority</label>
                <select name="priority" id="priority" class="form-select">
                    @php $p = old('priority', $task->priority ?? 'medium'); @endphp
                    <option value="low" {{ $p == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ $p == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ $p == 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ $p == 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="due_at" class="form-label fw-bold"><i class="fas fa-calendar text-success"></i> Due Date</label>
                <input type="date" name="due_at" id="due_at" class="form-control" value="{{ old('due_at', isset($task) && $task->due_at ? $task->due_at->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label fw-bold"><i class="fas fa-info-circle text-info"></i> Status</label>
                <select name="status" id="status" class="form-select">
                    @php $s = old('status', $task->status ?? 'open'); @endphp
                    <option value="open" {{ $s == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $s == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $s == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="closed" {{ $s == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" />
<style>
    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
        padding: 2px 8px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for the assigned_to, designations, departments, team_lead, and exemptions fields
        $('#assigned_to, #designations, #departments, #team_lead_id, #exemptions').select2({
            placeholder: 'Select employees to assign',
            allowClear: true,
            width: '100%',
            templateResult: function(data) {
                // Keep original display for most options
                if (!data.id) { return data.text; }
                
                // Special handling for "My Self" option
                if (data.id === 'self') {
                    return $('<span><i class="fas fa-user"></i> My Self</span>');
                }
                
                // For employee options, show with user icon
                return $('<span><i class="fas fa-user"></i> ' + data.text + '</span>');
            },
            templateSelection: function(data) {
                // Special handling for selected "My Self"
                if (data.id === 'self') {
                    return 'My Self';
                }
                return data.text;
            }
        });

        // Toggle between applicability (designations/departments) and direct employees
        function toggleAssignMode() {
            const mode = $('input[name="assign_mode"]:checked').val();
            if (mode === 'direct') {
                $('#col_assigned_to').show();
                $('#designations').closest('.col-md-4').hide();
                $('#departments').closest('.col-md-4').hide();
                $('#col_team_lead').hide();
                $('#exemptions').closest('.col-md-3').hide();
            } else {
                $('#col_assigned_to').hide();
                $('#designations').closest('.col-md-4').show();
                $('#departments').closest('.col-md-4').show();
                $('#col_team_lead').show();
                $('#exemptions').closest('.col-md-3').show();
            }
        }

        // initial toggle
        toggleAssignMode();

        $('input[name="assign_mode"]').on('change', function() {
            toggleAssignMode();
        });
    });
</script>
@endpush
