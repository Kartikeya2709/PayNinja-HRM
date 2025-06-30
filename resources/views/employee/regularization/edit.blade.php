@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">Review Regularization Request</div>

                <div class="card-body">
                    <p><strong>Employee:</strong> {{ $request->employee->name }}</p>
                    <p><strong>Date:</strong> {{ $request->date }}</p>
                    <p><strong>Check-in:</strong> {{ $request->check_in ?? 'N/A' }}</p>
                    <p><strong>Check-out:</strong> {{ $request->check_out ?? 'N/A' }}</p>
                    <p><strong>Reason:</strong> {{ $request->reason }}</p>

                    <hr>

                    <form action="{{ route('regularization.requests.update', $request->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="status">Action</label>
                            <select name="status" id="status" class="form-control">
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reason">Rejection Reason (if applicable)</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a href="{{ route('regularization.requests.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
