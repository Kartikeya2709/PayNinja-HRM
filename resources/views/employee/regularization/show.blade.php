@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">Request Details</div>

                <div class="card-body">
                    <p><strong>Employee:</strong> {{ $request->employee->name }}</p>
                    <p><strong>Date:</strong> {{ $request->date }}</p>
                    <p><strong>Check-in:</strong> {{ $request->check_in ?? 'N/A' }}</p>
                    <p><strong>Check-out:</strong> {{ $request->check_out ?? 'N/A' }}</p>
                    <p><strong>Reason:</strong> {{ $request->reason }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($request->status) }}</p>
                    <p><strong>Approved By:</strong> {{ $request->approver->name ?? 'N/A' }}</p>

                    {{-- <a href="{{ route('regularization-requests.index') }}" class="btn btn-secondary">Back to List</a> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
