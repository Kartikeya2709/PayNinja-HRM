@extends('layouts.app')

@section('content')
<div class="row mt-4">
    <div class="col-lg-12 px-1 cash-dep">
        <div class="card">
            <h5 class="card-header">Announcement List</h5>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $i => $announcement)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $announcement->title }}</td>
                                <td>{{ $announcement->description }}</td>
                                <td>{{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d') : '-' }}</td>
                                <td>
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        if ($announcement->publish_date && $now->lt($announcement->publish_date)) {
                                            $status = ['Upcoming', 'info'];
                                        } elseif ($announcement->expires_at && $now->gt($announcement->expires_at)) {
                                            $status = ['Completed', 'success'];
                                        } else {
                                            $status = ['Ongoing', 'warning'];
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $status[1] }}">{{ $status[0] }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('company-admin.announcements.show', $announcement->id) }}" class="btn btn-sm btn-info">Show</a>
                                    <a href="{{ route('company-admin.announcements.edit', $announcement->id) }}" class="btn btn-sm btn-primary ms-1">Edit</a>
                                    <form action="{{ route('company-admin.announcements.destroy', $announcement->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger ms-1" onclick="return confirm('Delete this announcement?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($announcements->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center">No announcements found.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection