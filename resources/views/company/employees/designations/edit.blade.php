@extends('layouts.app')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<div class="section container">
     <div class="section-header">
            <h1>Edit Designation</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="#">Edit Designation</a></div>
            </div>
        </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header justify-content-center mb-2">
                    <h3 class="card-title">Edit Designation</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('designations.update', ['encryptedId' => Crypt::encrypt($designation->id)]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                               <div class="form-group mb-4">
                                  <label for="title">Title<span class="text-danger">*</span></label>
                                  <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $designation->title) }}" required>
                                  @error('title')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                  @enderror
                               </div>
                               </div>
                            <div class="col-md-6 col-sm-12">
                               <div class="form-group mb-4">
                                  <label for="level">Level<span class="text-danger">*</span></label>
                                  <input type="text" name="level" id="level" class="form-control @error('level') is-invalid @enderror" value="{{ old('level', $designation->level) }}" required>
                                  <small class="form-text text-muted">Enter the designation level (e.g., Employee, Team Lead, Manager, etc.)</small>
                                  @error('level')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                  @enderror
                               </div>
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-6 col-sm-12">
                               <div class="form-group mb-4">
                                  <label for="department_id" class="form-label">Department<span class="text-danger">*</span></label>
                                  <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                  <option value="">Select Department</option>
                                  @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $designation->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                  @endforeach
                                  </select>
                                  @error('department_id')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                  @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12">
                               <div class="form-group mb-4">
                                  <label for="description">Description</label>
                                  <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $designation->description) }}</textarea>
                                  @error('description')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                  @enderror
                               </div>
                            </div>
                        <div class="d-flex gap-3 justify-content-center">
                           <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                 <i class="bi bi-save me-2"></i>Update Designation
                           </button>
                           <a href="{{ route('designations.index') }}" class="btn btn-danger px-4 rounded-pill">
                                 <i class="bi bi-x-circle me-2"></i>Cancel
                           </a>
                        </div>
                      </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
