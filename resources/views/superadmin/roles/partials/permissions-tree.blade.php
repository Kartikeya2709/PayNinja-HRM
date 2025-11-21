@if($slugs->count() > 0)
<div class="permissions-container">

    {{-- 🔘 Select All --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <input type="checkbox" id="select-all-permissions" class="form-check-input me-2">
                <label for="select-all-permissions" class="fw-bold mb-0 text-success">
                    <i class="fas fa-check-circle me-2"></i> Select All Permissions
                </label>
            </div>
        </div>
    </div>

    {{-- 🔘 Permission List --}}
    @foreach($slugs as $slug)
        @if(!$slug->parent)
        <div class="permission-card card mb-3 shadow-sm border-0">
            <div class="card-header bg-white border-bottom permission-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <input type="checkbox"
                           name="permissions[{{ $slug->slug }}]"
                           value="true"
                           id="permission_{{ $slug->slug }}"
                           class="form-check-input permission-main-checkbox me-2"
                           data-permission="{{ $slug->slug }}"
                           {{ (isset($selectedSlugs[$slug->slug]) && $selectedSlugs[$slug->slug]) ? 'checked' : '' }}>
                    <label for="permission_{{ $slug->slug }}" class="fw-bold mb-0 permission-label">
                        @if($slug->icon)
                            <i class="{{ $slug->icon }} me-2 text-success"></i>
                        @endif
                        {{ $slug->name }}
                    </label>
                </div>

                <span class="text-muted small" id="count-{{ $slug->slug }}">
                    @php
                        $childCount = $slug->children ? $slug->children->count() : 0;
                        $selectedCount = 0;
                        if ($slug->children) {
                            foreach ($slug->children as $child) {
                                if (isset($selectedSlugs[$child->slug]) && $selectedSlugs[$child->slug]) {
                                    $selectedCount++;
                                }
                            }
                        }
                    @endphp
                    {{ $selectedCount }} of {{ $childCount }} selected
                </span>
            </div>

            {{-- Always visible children --}}
            @if($slug->children && $slug->children->count() > 0)
            <div class="card-body">
                <div class="row">
                    @foreach($slug->children->sortBy('sort_order') as $child)
                    <div class="col-md-4 col-sm-6 mb-2">
                        <div class="form-check">
                            <input type="checkbox"
                                   name="permissions[{{ $child->slug }}]"
                                   value="true"
                                   id="permission_{{ $child->slug }}"
                                   class="form-check-input permission-sub-checkbox"
                                   data-parent-permission="{{ $slug->slug }}"
                                   {{ (isset($selectedSlugs[$child->slug]) && $selectedSlugs[$child->slug]) ? 'checked' : '' }}>
                            <label for="permission_{{ $child->slug }}" class="form-check-label">
                                {{ $child->name }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif
    @endforeach
</div>
@endif

<style>
.permissions-container {
    max-height: 650px;
    overflow-y: auto;
    padding-right: 5px;
}

/* Card style */
.permission-card {
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.permission-card:hover {
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
}

/* Header */
.permission-header {
    background: #f8f9fa;
    padding: 14px 20px;
    border-radius: 10px 10px 0 0;
}

/* Checkboxes */
.form-check-input {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    cursor: pointer;
    border: 1.5px solid #ccc;
    transition: all 0.2s ease-in-out;
}

.form-check-input:checked {
    background-color: #10b981;
    border-color: #10b981;
}

.form-check-input:hover {
    transform: scale(1.1);
}

/* Labels */
.permission-label {
    font-size: 16px;
    color: #111827;
}

.form-check-label {
    font-size: 14px;
    color: #374151;
    cursor: pointer;
}

/* Count badge */
.text-muted.small {
    background: #f1f3f5;
    padding: 4px 10px;
    border-radius: 20px;
}

/* Body */
.card-body {
    background: #fff;
    padding: 18px 22px;
    border-radius: 0 0 10px 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .col-md-4 {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 🧮 Update subpermission count
    function updatePermissionCount(permissionSlug) {
        const countElement = document.getElementById(`count-${permissionSlug}`);
        const subCheckboxes = document.querySelectorAll(`input[data-parent-permission="${permissionSlug}"]`);
        let checkedCount = 0;

        subCheckboxes.forEach(cb => {
            if (cb.checked) checkedCount++;
        });

        const totalCount = subCheckboxes.length;
        if (countElement) {
            countElement.textContent = `${checkedCount} of ${totalCount} selected`;
        }
    }

    // 🔘 Select All toggle
    const selectAll = document.getElementById('select-all-permissions');
    selectAll.addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.permission-main-checkbox, .permission-sub-checkbox').forEach(cb => {
            cb.checked = isChecked;
        });

        document.querySelectorAll('.permission-main-checkbox').forEach(cb => {
            updatePermissionCount(cb.dataset.permission);
        });
    });

    // 🔄 Auto update select all checkbox
    function updateSelectAllStatus() {
        const allCheckboxes = document.querySelectorAll('.permission-main-checkbox, .permission-sub-checkbox');
        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;
    }

    // 🧩 Handle parent/sub interactions
    document.addEventListener('change', function(e) {
        // Parent checkbox
        if (e.target.classList.contains('permission-main-checkbox')) {
            const permissionSlug = e.target.dataset.permission;
            const isChecked = e.target.checked;
            const subCheckboxes = document.querySelectorAll(`input[data-parent-permission="${permissionSlug}"]`);
            subCheckboxes.forEach(cb => cb.checked = isChecked);
            updatePermissionCount(permissionSlug);
            updateSelectAllStatus();
        }

        // Sub checkbox
        if (e.target.classList.contains('permission-sub-checkbox')) {
            const parentPermission = e.target.dataset.parentPermission;
            const parent = document.querySelector(`input[data-permission="${parentPermission}"]`);
            const subCheckboxes = document.querySelectorAll(`input[data-parent-permission="${parentPermission}"]`);
            const allChecked = Array.from(subCheckboxes).every(cb => cb.checked);
            const anyChecked = Array.from(subCheckboxes).some(cb => cb.checked);
            parent.checked = allChecked || anyChecked;
            updatePermissionCount(parentPermission);
            updateSelectAllStatus();
        }
    });

    // 🧾 Initialize counts and select all state
    document.querySelectorAll('.permission-main-checkbox').forEach(cb => {
        updatePermissionCount(cb.dataset.permission);
    });
    updateSelectAllStatus();
});
</script>