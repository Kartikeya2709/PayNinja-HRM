@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Lead</h3>
                    <div class="card-tools">
                        <a href="{{ route('company-admin.leads.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
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

                    <form action="{{ route('company-admin.leads.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                               <div class="form-group mb-4">
                                  <label for="name">Name <span class="text-danger">*</span></label>
                                  <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                  value="{{ old('name') }}" required>
                                  @error('name')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                  @enderror
                               </div>
                            </div>

                            <div class="col-md-6 col-sm-12">
                               <div class="form-group mb-4">
                                  <label for="email">Email <span class="text-danger">*</span></label>
                                  <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                  value="{{ old('email') }}" required>
                                  @error('email')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                  @enderror
                               </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                               <div class="form-group mb-4">
                                  <label for="phone">Phone</label>
                                  <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                  value="{{ old('phone') }}">
                                  @error('phone')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                               </div>
                            </div>
                       
                            <div class="col-md-6 col-sm-12">
                               <div class="form-group mb-4">
                                  <label for="status">Status <span class="text-danger">*</span></label>
                                  <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                  @foreach(['new', 'contacted', 'qualified', 'lost'] as $status)
                                  <option value="{{ $status }}" {{ old('status', 'new') == $status ? 'selected' : '' }}>
                                  {{ ucfirst($status) }}
                                  </option>
                                  @endforeach
                                  </select>
                                  @error('status')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                  @enderror
                               </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                               <div class="form-group mb-4">
                                  <label for="message">Message</label>
                                  <textarea name="message" id="message" rows="4" class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                                  @error('message')
                                  <span class="invalid-feedback">{{ $message }}</span>
                                  @enderror
                               </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-center">
                           <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                           <i class="bi bi-save me-2"></i>Create Lead
                           </button>
                           <a href="{{ route('company-admin.leads.index') }}" class="btn btn-danger px-4 rounded-pill shadow-sm">
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