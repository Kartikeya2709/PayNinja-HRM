@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Role Management</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Role Management</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4>Roles List</h4>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('company.roles.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create New Role
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
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

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" id="search-input" class="form-control" 
                                               placeholder="Search roles..." value="{{ request('search') }}">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select id="status-filter" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="clear-filters" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <!-- Loading indicator moved to table section -->
                                </div>
                            </div>

                            <div id="roles-table-container">
                                @include('company.roles._table', ['roles' => $roles])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Delete Modals -->
@foreach($roles as $role)
<div class="modal fade" id="deleteModal{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the role <strong>{{ $role->name }}</strong>? 
                This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('company.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;
    const searchInput = $('#search-input');
    const statusFilter = $('#status-filter');
    const clearFilters = $('#clear-filters');
    const tableLoading = $('#table-loading');
    const tableContainer = $('#roles-table-container');

    // Debounced search function
    function performSearch() {
        const searchTerm = searchInput.val();
        const status = statusFilter.val();
        
        // Show loading indicator inside table
        tableLoading.show();
        
        // Make AJAX request
        $.ajax({
            url: '{{ route("company.roles.index") }}',
            method: 'GET',
            data: {
                search: searchTerm,
                is_active: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                tableContainer.html(response.html);
                tableLoading.hide();
            },
            error: function(xhr) {
                console.error('Search failed:', xhr);
                tableLoading.hide();
                // Show error message
                alert('Search failed. Please try again.');
            }
        });
    }

    // Search input with debouncing (300ms delay)
    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300);
    });

    // Status filter change
    statusFilter.on('change', function() {
        performSearch();
    });

    // Clear filters
    clearFilters.on('click', function() {
        searchInput.val('');
        statusFilter.val('');
        performSearch();
    });

    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const searchTerm = searchInput.val();
        const status = statusFilter.val();
        
        // Show loading indicator inside table
        tableLoading.show();
        
        $.ajax({
            url: url,
            method: 'GET',
            data: {
                search: searchTerm,
                is_active: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                tableContainer.html(response.html);
                tableLoading.hide();
            },
            error: function(xhr) {
                console.error('Pagination failed:', xhr);
                tableLoading.hide();
                alert('Failed to load page. Please try again.');
            }
        });
    });
});
</script>
@endpush