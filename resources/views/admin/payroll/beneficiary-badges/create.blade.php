@extends('layouts.app')

@section('title', 'Create New Beneficiary Badge')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>New Beneficiary Badge</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="#">New Beneficiary Badge</a></div>
            </div>
        </div>
    <div class="row">
      
        <div class="col-lg-12">
              <div class="card">
            
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Create New Beneficiary Badge</h5>
                     <a href="{{ route('admin.payroll.beneficiary-badges.index') }}" class="btn btn-secondary add-list"><i class="fa-solid fa-arrow-left me-2"></i>Back to List</a>
             
                </div>
               
           
                   <p class="mb-3">Define a new allowance or deduction badge for payroll.</p>
        

      
                <div class="card-body">
                    <form action="{{ route('admin.payroll.beneficiary-badges.store') }}" method="POST">
                        @csrf
                        
                        @include('admin.payroll.beneficiary-badges._form')

                        <div class="mt-2 text-center">
                            <button type="submit" class="btn btn-primary">Create Badge</button>
                            <a href="{{ route('admin.payroll.beneficiary-badges.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
