@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-center">
                    <h3 class="card-title">{{ isset($category) ? 'Edit Category' : 'Create Category' }}</h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ isset($category) ? route('admin.assets.categories.update', $category->id) : route('admin.assets.categories.store') }}" method="POST">
                    @csrf
                    @if(isset($category))
                    @method('PUT')
                    @endif

                    <!-- Name Field -->
                    <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                    class="form-control modern-input @error('name') is-invalid @enderror"
                    value="{{ old('name', $category->name ?? '') }}" required
                    placeholder="Enter category name">
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>

                   <!-- Description Field -->
                   <div class="mb-4">
                   <label for="description" class="form-label fw-semibold">Description</label>
                   <textarea id="description" name="description" rows="3"
                   class="form-control modern-input @error('description') is-invalid @enderror"
                   placeholder="Write a short description...">{{ old('description', $category->description ?? '') }}</textarea>
                   @error('description')
                   <div class="invalid-feedback">{{ $message }}</div>
                   @enderror
                   </div>

                  <!-- Buttons -->
                  <div class="d-flex gap-3 justify-content-center">
                  <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                  <i class="bi bi-save me-2"></i>{{ isset($category) ? 'Update' : 'Create' }}
                  </button>
                  <a href="{{ route('admin.assets.categories.index') }}" class="btn btn-danger px-4 rounded-pill">
                  <i class="bi bi-x-circle me-2"></i>Cancel
                  </a>
                  </div>
                  </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection