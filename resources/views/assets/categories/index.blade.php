@extends('layouts.app')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Asset Categories</h3>
                    @if(\App\Models\User::hasAccess('assets/asset-category-create', true))
                    <a href="{{ route('assets.categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Category
                    </a>
                    @endif
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
                                    @if(\App\Models\User::hasAccess('assets/asset-category-show/{encryptedId}', true) ||
                                        \App\Models\User::hasAccess('assets/asset-category-edit/{encryptedId}', true) ||
                                        \App\Models\User::hasAccess('assets/asset-category-delete/{encryptedId}', true))
                                    <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->description }}</td>
                                    <td>{{ $category->assets_count }}</td>
                                    @if(\App\Models\User::hasAccess('assets/asset-category-show/{encryptedId}', true) ||
                                        \App\Models\User::hasAccess('assets/asset-category-edit/{encryptedId}', true) ||
                                        \App\Models\User::hasAccess('assets/asset-category-delete/{encryptedId}', true))
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                          @if(\App\Models\User::hasAccess('assets/asset-category-show/{encryptedId}', true))
                                          <a href="{{ route('assets.categories.show', ['encryptedId' => Crypt::encrypt($category->id)]) }}"
                                          class="btn btn-outline-info btn-sm action-btn"
                                          data-id="{{ $category->id }}" data-bs-toggle="tooltip"
                                          data-bs-placement="top" title="View Category" aria-label="View">
                                          <span class="btn-content">
                                              <i class="fas fa-eye"></i>
                                          </span>
                                          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                          </a>
                                          @endif

                                          @if(\App\Models\User::hasAccess('assets/asset-category-edit/{encryptedId}', true))
                                          <a href="{{ route('assets.categories.edit', ['encryptedId' => Crypt::encrypt($category->id)]) }}"
                                          class="btn btn-outline-primary btn-sm action-btn"
                                          data-id="{{ $category->id }}" data-bs-toggle="tooltip"
                                          data-bs-placement="top" title="Edit Category" aria-label="Edit">
                                          <span class="btn-content">
                                              <i class="fas fa-edit"></i>
                                          </span>
                                          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                          </a>
                                          @endif

                                          @if(\App\Models\User::hasAccess('assets/asset-category-delete/{encryptedId}', true))
                                          <form action="{{ route('assets.categories.destroy', ['encryptedId' => Crypt::encrypt($category->id)]) }}" method="POST" class="d-inline-block">
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
                                          @endif
                                        </div>

                                    </td>
                                    @endif
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
