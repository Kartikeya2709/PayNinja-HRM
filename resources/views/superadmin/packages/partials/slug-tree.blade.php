@if($slugs->count() > 0)
<div class="modules-container">

    {{-- 🔘 Select All --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <input type="checkbox" id="select-all" class="form-check-input me-2">
                <label for="select-all" class="fw-bold mb-0 text-success">
                    <i class="fas fa-check-circle me-2"></i> Select All Modules
                </label>
            </div>
        </div>
    </div>

    {{-- 🔘 Module List --}}
    @foreach($slugs as $slug)
        @if(!$slug->parent)
        <div class="module-card card mb-3 shadow-sm border-0">
            <div class="card-header bg-white border-bottom module-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <input type="checkbox"
                           name="modules[{{ $slug->slug }}]"
                           value="1"
                           id="module_{{ $slug->slug }}"
                           class="form-check-input module-main-checkbox me-2"
                           data-module="{{ $slug->slug }}"
                           {{ (isset($selectedModules[$slug->slug]) && $selectedModules[$slug->slug] === true) ? 'checked' : '' }}>
                    <label for="module_{{ $slug->slug }}" class="fw-bold mb-0 module-label">
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
                                if (isset($selectedModules[$child->slug]) && $selectedModules[$child->slug] === true) {
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
                                   name="modules[{{ $child->slug }}]"
                                   value="1"
                                   id="module_{{ $child->slug }}"
                                   class="form-check-input module-sub-checkbox"
                                   data-parent-module="{{ $slug->slug }}"
                                   {{ (isset($selectedModules[$child->slug]) && $selectedModules[$child->slug] === true) ? 'checked' : '' }}>
                            <label for="module_{{ $child->slug }}" class="form-check-label">
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
.modules-container {
    max-height: 650px;
    overflow-y: auto;
    padding-right: 5px;
}

/* Card style */
.module-card {
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.module-card:hover {
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
}

/* Header */
.module-header {
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
.module-label {
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
    // 🧮 Update submodule count
    function updateModuleCount(moduleSlug) {
        const countElement = document.getElementById(`count-${moduleSlug}`);
        const subCheckboxes = document.querySelectorAll(`input[data-parent-module="${moduleSlug}"]`);
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
    const selectAll = document.getElementById('select-all');
    selectAll.addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.module-main-checkbox, .module-sub-checkbox').forEach(cb => {
            cb.checked = isChecked;
        });

        document.querySelectorAll('.module-main-checkbox').forEach(cb => {
            updateModuleCount(cb.dataset.module);
        });
    });

    // 🔄 Auto update select all checkbox
    function updateSelectAllStatus() {
        const allCheckboxes = document.querySelectorAll('.module-main-checkbox, .module-sub-checkbox');
        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;
    }

    // 🧩 Handle parent/sub interactions
    document.addEventListener('change', function(e) {
        // Parent checkbox
        if (e.target.classList.contains('module-main-checkbox')) {
            const moduleSlug = e.target.dataset.module;
            const isChecked = e.target.checked;
            const subCheckboxes = document.querySelectorAll(`input[data-parent-module="${moduleSlug}"]`);
            subCheckboxes.forEach(cb => cb.checked = isChecked);
            updateModuleCount(moduleSlug);
            updateSelectAllStatus();
        }

        // Sub checkbox
        if (e.target.classList.contains('module-sub-checkbox')) {
            const parentModule = e.target.dataset.parentModule;
            const parent = document.querySelector(`input[data-module="${parentModule}"]`);
            const subCheckboxes = document.querySelectorAll(`input[data-parent-module="${parentModule}"]`);
            const allChecked = Array.from(subCheckboxes).every(cb => cb.checked);
            const anyChecked = Array.from(subCheckboxes).some(cb => cb.checked);
            parent.checked = allChecked || anyChecked;
            updateModuleCount(parentModule);
            updateSelectAllStatus();
        }
    });

    // 🧾 Initialize counts and select all state
    document.querySelectorAll('.module-main-checkbox').forEach(cb => {
        updateModuleCount(cb.dataset.module);
    });
    updateSelectAllStatus();
});
</script>
