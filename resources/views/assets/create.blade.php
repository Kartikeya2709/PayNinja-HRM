@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-center">
                    <h3 class="card-title">Edit Asset</h3>
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

                    <form action="{{ isset($asset) ? route('admin.assets.update', $asset->id) : route('admin.assets.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($asset))
                            @method('PUT')
                        @endif
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4">
                               <div class="form-group">
                               <label for="name">Name <span class="text-danger">*</span></label>
                               <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name', $asset->name ?? '') }}" required>
                               </div>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                               <div class="form-group">
                               <label for="category_id">Category <span class="text-danger">*</span></label>
                               <select class="form-control @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" 
                                        {{ old('category_id', $asset->category_id ?? '') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                                </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4">
                                <div class="form-group">
                                    <label for="purchase_cost">Purchase Cost</label>
                                    <input type="number" step="0.01" class="form-control @error('purchase_cost') is-invalid @enderror" 
                                           id="purchase_cost" name="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <div class="form-group">
                                    <label for="purchase_date">Purchase Date</label>
                                    <input type="date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                           id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date ?? '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                           <div class="col-md-6 col-sm-12 mb-4">
                               <div class="form-group">
                               <label for="description">Description</label>
                               <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="3">{{ old('description', $asset->description ?? '') }}</textarea>
                               </div>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                               <div class="form-group">
                               <label for="condition">Condition <span class="text-danger">*</span></label>
                               <select class="form-control @error('condition') is-invalid @enderror" 
                                    id="condition" name="condition" required>
                                <option value="">Select Condition</option>
                                @php
                                    $conditions = ['good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'damaged' => 'Damaged'];
                                @endphp
                                @foreach($conditions as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ old('condition', $asset->condition ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        </div>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $asset->notes ?? '') }}</textarea>
                        </div>

                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                               <i class="bi bi-save me-2"></i>{{ isset($asset) ? 'Update' : 'Create' }} Asset
                            </button>
                            <a href="{{ route('admin.assets.index') }}" class="btn btn-danger px-4 rounded-pill">
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