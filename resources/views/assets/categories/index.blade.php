@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Asset Categories</h3>
                    <a href="{{ route('admin.assets.categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Category
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Total Assets</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->description }}</td>
                                    <td>{{ $category->assets_count }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                          <a href="{{ route('admin.assets.categories.show', $category->id) }}"
                                          class="btn btn-outline-info btn-sm action-btn"
                                          data-id="{{ $category->id }}" data-bs-toggle="tooltip"
                                          data-bs-placement="top" title="View Category" aria-label="View">
                                          <span class="btn-content">
                                              <i class="fas fa-eye"></i>
                                          </span>
                                          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                          </a>

                                          <a href="{{ route('admin.assets.categories.edit', $category->id) }}"
                                          class="btn btn-outline-primary btn-sm action-btn"
                                          data-id="{{ $category->id }}" data-bs-toggle="tooltip"
                                          data-bs-placement="top" title="Edit Category" aria-label="Edit">
                                          <span class="btn-content">
                                              <i class="fas fa-edit"></i>
                                          </span>
                                          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                          </a>

                                          <form action="{{ route('admin.assets.categories.destroy', $category->id) }}" method="POST" class="d-inline-block">
                                          @csrf
                                          @method('DELETE')
                                          <button type="submit"
                                          class="btn btn-outline-danger btn-sm action-btn rounded-start-0"
                                          data-id="{{ $category->id }}" data-bs-toggle="tooltip"
                                          data-bs-placement="top" title="Delete Category" aria-label="Delete"
                                          onclick="return confirm('Are you sure you want to delete this category?')">
                                          <span class="btn-content">
                                             <i class="fas fa-trash"></i>
                                          </span>
                                          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                          </button>
                                          </form>
                                        </div>

                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No asset categories found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection