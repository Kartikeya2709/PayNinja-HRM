@extends('layouts.app')

@section('title', 'Edit Leave Balance')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Leave Balance</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('company.leave-balances.index') }}">Leave Balances</a></div>
            <div class="breadcrumb-item active"><a href="">Edit</a></div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('company.leave-balances.update', $leaveBalance->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label>Employee</label>
                                <p class="form-control-static">{{ $leaveBalance->employee->name }}</p>
                            </div>

                            <div class="form-group">
                                <label>Leave Type</label>
                                <p class="form-control-static">{{ $leaveBalance->leaveType->name }}</p>
                            </div>

                            <div class="form-group">
                                <label for="total_days">Total Days <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="total_days" 
                                       id="total_days" 
                                       class="form-control @error('total_days') is-invalid @enderror" 
                                       value="{{ old('total_days', $leaveBalance->total_days) }}" 
                                       min="0" 
                                       required>
                                @error('total_days')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Used Days</label>
                                <p class="form-control-static">{{ $leaveBalance->used_days }}</p>
                            </div>

                            <div class="form-group">
                                <label>Remaining Days</label>
                                <p class="form-control-static">{{ $leaveBalance->remaining_days }}</p>
                            </div>

                            <div class="form-group">
                                <label for="year">Year <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="year" 
                                       id="year" 
                                       class="form-control @error('year') is-invalid @enderror" 
                                       value="{{ old('year', $leaveBalance->year) }}" 
                                       required>
                                @error('year')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Update Leave Balance</button>
                                <a href="{{ route('company.leave-balances.index') }}" class="btn btn-link">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
