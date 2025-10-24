<form action="{{ route('superadmin.setting.slug.edit', $slug->id) }}" method="POST" id="editSlugForm">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="edit_name" name="name" value="{{ old('name', $slug->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="edit_slug" class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror"
                           id="edit_slug" name="slug" value="{{ old('slug', $slug->slug) }}" required>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="edit_icon" class="form-label">Icon</label>
                    <input type="text" class="form-control @error('icon') is-invalid @enderror"
                           id="edit_icon" name="icon" value="{{ old('icon', $slug->icon) }}"
                           placeholder="e.g., fas fa-home">
                    @error('icon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="edit_parent" class="form-label">Parent</label>
                    <select class="form-control @error('parent') is-invalid @enderror"
                            id="edit_parent" name="parent">
                        <option value="">Select Parent (Optional)</option>
                        @foreach($slug_list as $parent_slug)
                            <option value="{{ $parent_slug->id }}"
                                    {{ (old('parent', $slug->parent_id) == $parent_slug->id) ? 'selected' : '' }}>
                                {{ $parent_slug->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="edit_is_visible" class="form-label">Is Visible <span class="text-danger">*</span></label>
                    <select class="form-control @error('is_visible') is-invalid @enderror"
                            id="edit_is_visible" name="is_visible" required>
                        <option value="1" {{ old('is_visible', $slug->is_visible) == 1 ? 'selected' : '' }}>Visible</option>
                        <option value="0" {{ old('is_visible', $slug->is_visible) == 0 ? 'selected' : '' }}>Hidden</option>
                    </select>
                    @error('is_visible')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="edit_sort_order" class="form-label">Sort Order <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                           id="edit_sort_order" name="sort_order" value="{{ old('sort_order', $slug->sort_order) }}" min="0" max="255" required>
                    @error('sort_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Slug</button>
    </div>
</form>