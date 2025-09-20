@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Pending Field Visit Approvals</h4>
                </div>
                <div class="card-body">
                    @if($visits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Employee</th>
                                        <th>Location</th>
                                        <th>Scheduled Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visits as $visit)
                                        <tr>
                                            <td>{{ $visit->visit_title }}</td>
                                            <td>{{ $visit->employee->name }}</td>
                                            <td>{{ $visit->location_name }}</td>
                                            <td>{{ $visit->scheduled_start_datetime->format('M d, Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('field-visits.show', $visit) }}" class="btn btn-sm btn-info">View</a>
                                                <form action="{{ route('field-visits.approve', $visit) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                <form action="{{ route('field-visits.reject', $visit) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>No pending approvals.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
