@extends('layouts.app')

@section('title', 'Edit Beneficiary Badge')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Edit Beneficiary Badge: {{ $beneficiaryBadge->name }}</h4>
                    <p class="mb-0">Modify the details of this allowance or deduction badge.</p>
                </div>
                <a href="{{ route('admin.payroll.beneficiary-badges.index') }}" class="btn btn-secondary add-list"><i class="fa-solid fa-arrow-left me-2"></i>Back to List</a>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.payroll.beneficiary-badges.update', \Crypt::encrypt($beneficiaryBadge->id)) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @include('admin.payroll.beneficiary-badges._form', ['beneficiaryBadge' => $beneficiaryBadge])

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Badge</button>
                            <a href="{{ route('admin.payroll.beneficiary-badges.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
