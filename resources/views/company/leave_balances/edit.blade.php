@extends('layouts.app')

@section('title', 'Edit Leave Balance')

@section('content')
<section class="section container">
    <div class="section-header">
        <h1>Edit Leave Balance</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('leaves.leave-balances.index') }}">Leave Balances</a></div>
            <div class="breadcrumb-item active"><a href="">Edit</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="leave-balance-card card">
    <div class="accent-bar mb-3"></div>
    <h5 class="fw-bold text-dark mb-4">
        <i class="fas fa-calendar-check me-2 text-primary"></i>Update Leave Balance
    </h5>

    <form action="{{ route('leaves.leave-balances.update', \Illuminate\Support\Facades\Crypt::encrypt($leaveBalance->id)) }}" method="POST" class="leave-balance-form">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted">Employee</label>
                <p class="form-control-plaintext fw-semibold text-dark">{{ $leaveBalance->employee->name }}</p>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted">Leave Type</label>
                <p class="form-control-plaintext fw-semibold text-dark">{{ $leaveBalance->leaveType->name }}</p>
            </div>

            <div class="col-md-6">
                <label for="total_days" class="form-label fw-semibold">Total Days <span class="text-danger">*</span></label>
                <input type="number"
                    name="total_days"
                    id="total_days"
                    class="form-control rounded-3 shadow-sm @error('total_days') is-invalid @enderror"
                    value="{{ old('total_days', $leaveBalance->total_days) }}"
                    min="0"
                    required>
                @error('total_days')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted">Used Days</label>
                <p class="form-control-plaintext fw-semibold text-dark">{{ $leaveBalance->used_days }}</p>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted">Remaining Days</label>
                <p class="form-control-plaintext fw-semibold text-dark">{{ $leaveBalance->remaining_days }}</p>
            </div>

            <div class="col-md-6">
                <label for="year" class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
                <input type="number"
                    name="year"
                    id="year"
                    class="form-control rounded-3 shadow-sm @error('year') is-invalid @enderror"
                    value="{{ old('year', $leaveBalance->year) }}"
                    required>
                @error('year')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 text-center">
            <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                <i class="bi bi-save me-2"></i> Update Leave Balance
            </button>
            <a href="{{ route('leaves.leave-balances.index') }}" class="btn btn-danger px-4 rounded-pill ms-2">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </a>
        </div>
    </form>
</div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
