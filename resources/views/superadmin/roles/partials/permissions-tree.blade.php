@if($slugs->count() > 0)
    <ul class="permissions-list" style="list-style: none; padding-left: {{ $level * 20 }}px;">
        @foreach($slugs as $slug)
            <li class="permission-item">
                <div class="form-check">
                    <input class="form-check-input permission-checkbox"
                           type="checkbox"
                           id="slug_{{ $slug->slug }}"
                           name="permissions[{{ $slug->slug }}]"
                           value="true"
                           data-slug="{{ $slug->slug }}"
                           data-parent="{{ $slug->parent ? $slug->parent->slug : '' }}"
                           {{ (isset($selectedSlugs[$slug->slug]) && $selectedSlugs[$slug->slug]) ? 'checked' : '' }}>
                    <label class="form-check-label" for="slug_{{ $slug->slug }}">
                        @if($slug->icon)
                            <i class="{{ $slug->icon }}"></i>
                        @endif
                        {{ $slug->slug }}
                    </label>
                </div>

                @if($slug->children && $slug->children->count() > 0)
                    @include('superadmin.roles.partials.permissions-tree', [
                        'slugs' => $slug->children->sortBy('sort_order'),
                        'selectedSlugs' => $selectedSlugs,
                        'level' => $level + 1
                    ])
                @endif
            </li>
        @endforeach
    </ul>
@endif

<style>
.permissions-tree .permissions-list .permission-item {
    margin-bottom: 8px;
}

.permissions-tree .permissions-list .permissions-list {
    margin-top: 8px;
}

.permissions-tree .form-check-label {
    font-weight: normal;
    cursor: pointer;
}

.permissions-tree .form-check-input:checked + .form-check-label {
    font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle parent-child checkbox relationships
    const checkboxes = document.querySelectorAll('.permission-checkbox');

    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const slugName = this.dataset.slug;
            const isChecked = this.checked;

            // Find all child checkboxes
            const childCheckboxes = document.querySelectorAll(`input[data-parent="${slugName}"]`);

            // Check/uncheck all children
            childCheckboxes.forEach(function(child) {
                child.checked = isChecked;
                child.dispatchEvent(new Event('change'));
            });

            // Check parent if any child is checked
            updateParentCheckboxes(this);
        });
    });

    function updateParentCheckboxes(checkbox) {
        const li = checkbox.closest('li');
        if (!li) return;

        const parentUl = li.closest('ul.permissions-list');
        if (!parentUl) return;

        const parentLi = parentUl.closest('li');
        if (!parentLi) return;

        const parentCheckbox = parentLi.querySelector('.permission-checkbox');
        if (!parentCheckbox) return;

        const siblingCheckboxes = parentUl.querySelectorAll('.permission-checkbox');
        const anyChecked = Array.from(siblingCheckboxes).some(cb => cb.checked);

        parentCheckbox.checked = anyChecked;
        updateParentCheckboxes(parentCheckbox);
    }
});
</script>