@extends('layouts.app')

@section('title', 'Manage Slugs')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Manage Slugs</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Settings</div>
                <div class="breadcrumb-item">Slugs</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Slugs Management</h2>
            <p class="section-lead mt-2">Manage hierarchical slugs for your application.</p>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header card-margin">
                    <h4>All Slugs</h4>
                    <div class="card-header-action">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlugModal">
                            <i class="fas fa-plus"></i> Add New Slug
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('superadmin.setting.slugs') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by name or slug..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('superadmin.setting.slugs') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Icon</th>
                                    <th>Parent</th>
                                    <th>Is Visible</th>
                                    <th>Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($slugs as $index => $slug)
                                    <tr>
                                        <td>{{ $slugs->firstItem() + $index }}</td>
                                        <td>{{ $slug->name }}</td>
                                        <td>{{ $slug->slug }}</td>
                                        <td>
                                            @if($slug->icon)
                                                <i class="{{ $slug->icon }}"></i> {{ $slug->icon }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $slug->parent ? $slug->parent->name : '-' }}</td>
                                        <td>
                                            <span class="badge {{ $slug->is_visible ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $slug->is_visible ? 'Visible' : 'Hidden' }}
                                            </span>
                                        </td>
                                        <td>{{ $slug->sort_order }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning edit-slug-btn"
                                                    data-id="{{ $slug->id }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editSlugModal">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="{{ route('superadmin.setting.slug.destroy', $slug->id) }}"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No slugs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($slugs->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $slugs->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add Slug Modal -->
<div class="modal fade" id="addSlugModal" tabindex="-1" aria-labelledby="addSlugModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSlugModalLabel">Add New Slug</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('superadmin.setting.slug.add') }}" method="POST" id="addSlugForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       id="slug" name="slug" value="{{ old('slug') }}" required>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon" class="form-label">Icon</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                       id="icon" name="icon" value="{{ old('icon') }}"
                                       placeholder="e.g., fas fa-home">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent" class="form-label">Parent</label>
                                <select class="form-control @error('parent') is-invalid @enderror"
                                        id="parent" name="parent">
                                    <option value="">Select Parent (Optional)</option>
                                    @foreach($slug_list as $parent_slug)
                                        <option value="{{ $parent_slug->id }}" {{ old('parent') == $parent_slug->id ? 'selected' : '' }}>
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
                                <label for="is_visible" class="form-label">Is Visible <span class="text-danger">*</span></label>
                                <select class="form-control @error('is_visible') is-invalid @enderror"
                                        id="is_visible" name="is_visible" required>
                                    <option value="1" {{ old('is_visible', 0) == 1 ? 'selected' : '' }}>Visible</option>
                                    <option value="0" {{ old('is_visible', 0) == 0 ? 'selected' : '' }}>Hidden</option>
                                </select>
                                @error('is_visible')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sort_order" class="form-label">Sort Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="255" required>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Slug</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Slug Modal -->
<div class="modal fade" id="editSlugModal" tabindex="-1" aria-labelledby="editSlugModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSlugModalLabel">Edit Slug</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="editSlugModalBody">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center p-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Edit slug button click
    $('.edit-slug-btn').on('click', function() {
        var slugId = $(this).data('id');
        var modalBody = $('#editSlugModalBody');

        modalBody.html('<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: '{{ route("superadmin.setting.slug.edit", ":id") }}'.replace(':id', slugId),
            type: 'GET',
            success: function(response) {
                modalBody.html(response);
            },
            error: function(xhr, status, error) {
                modalBody.html('<div class="alert alert-danger">Error loading form: ' + error + '</div>');
            }
        });
    });

    // Handle edit form submission
    $(document).on('submit', '#editSlugForm', function(e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editSlugModal').modal('hide');
                    location.reload();
                } else {
                    // Handle validation errors
                    if (response.errors) {
                        form.find('.is-invalid').removeClass('is-invalid');
                        form.find('.invalid-feedback').remove();

                        $.each(response.errors, function(field, messages) {
                            var fieldElement = form.find('[name="' + field + '"]');
                            fieldElement.addClass('is-invalid');
                            fieldElement.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'An error occurred while updating the slug.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush
@endsection