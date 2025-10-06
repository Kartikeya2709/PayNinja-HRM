@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Pending Field Visit Approvals</h5>
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
                                        <div class="btn-group btn-group-sm">
                                        <a href="{{ route('field-visits.show', $visit) }}"
                                            class="btn btn-outline-info btn-sm action-btn"
                                            data-id="{{ $visit->id ?? '' }}" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="View Visit" aria-label="View">
                                            <span class="btn-content">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                                aria-hidden="true"></span>
                                        </a>

                                        <form action="{{ route('field-visits.approve', $visit) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-outline-success btn-sm action-btn rounded-0"
                                                data-id="{{ $request->id ?? '' }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Approve Request" aria-label="Approve">
                                                <span class="btn-content">
                                                    <i class="fa-solid fa-check"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                    aria-hidden="true"></span>
                                            </button>

                                        </form>
                                        <form action="{{ route('field-visits.reject', $visit) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-outline-danger btn-sm action-btn rounded-start-0"
                                                data-id="{{ $request->id ?? '' }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Reject Request" aria-label="Reject">
                                                <span class="btn-content">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                    aria-hidden="true"></span>
                                            </button>

                                        </form>
                                        </div>
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