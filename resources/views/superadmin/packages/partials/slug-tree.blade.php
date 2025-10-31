@if($slugs->count() > 0)
    <ul class="permissions-list" style="list-style: none; padding-left: {{ $level * 20 }}px;">
        @foreach($slugs as $slug)
            <li class="permission-item">
                <div class="module-item" style="margin-bottom: 5px;">
                    <input type="checkbox"
                           name="modules[]"
                           value="{{ $slug->slug }}"
                           id="module_{{ $slug->slug }}"
                           data-slug="{{ $slug->slug }}"
                           data-parent="{{ $slug->parent ? $slug->parent->slug : '' }}"
                           {{ in_array($slug->slug, $selectedSlugs) ? 'checked' : '' }}>
                    <label for="module_{{ $slug->slug }}" style="margin-left: 5px;">
                        @if($slug->icon)
                            <i class="{{ $slug->icon }}"></i>
                        @endif
                        {{ $slug->name }} ({{ $slug->slug }})
                    </label>
                </div>

                @if($slug->children && $slug->children->count() > 0)
                    @include('superadmin.packages.partials.slug-tree', [
                        'slugs' => $slug->children->sortBy('sort_order'),
                        'selectedSlugs' => $selectedSlugs,
                        'level' => $level + 1
                    ])
                @endif
            </li>
        @endforeach
    </ul>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle parent-child checkbox relationships
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name="modules[]"]')) {
            const checkbox = e.target;
            const slugName = checkbox.dataset.slug;
            const isChecked = checkbox.checked;

            // Find all child checkboxes
            const childCheckboxes = document.querySelectorAll(`input[data-parent="${slugName}"]`);

            // If parent is checked, check all children
            // If parent is unchecked, uncheck all children
            childCheckboxes.forEach(child => {
                child.checked = isChecked;
            });
        }
    });
});
</script>