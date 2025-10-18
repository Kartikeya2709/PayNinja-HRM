@extends('layouts.app')

@section('title', 'Contact Messages')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Contact Messages</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Message Date</th>
                                    <th>Additional Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contactMessages as $message)
                                    <tr>
                                        <td>{{ $message->id }}</td>
                                        <td>{{ $message->name }}</td>
                                        <td>{{ $message->email }}</td>
                                        <td>{{ $message->phone ?: 'N/A' }}</td>
                                        <td>{{ $message->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <div class="truncate-text" title="{{ $message->additional_info }}">
                                                {{ Str::limit($message->additional_info, 50) }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No contact messages found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.truncate-text {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
@endsection
