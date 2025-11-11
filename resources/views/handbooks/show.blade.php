@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $handbook->title }}</h4>
                    <div class="">
                    <a href="{{ route('handbooks.index') }}" class="btn btn-secondary">Back to List</a>
                    @if(Auth::user()->hasRole(['admin', 'company_admin']))
                        <a href="{{ route('handbooks.edit', $handbook) }}" class="btn btn-warning">Edit</a>
                    @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Details</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Title:</th>
                                    <td>{{ $handbook->title }}</td>
                                </tr>
                                <tr>
                                    <th>Version:</th>
                                    <td>{{ $handbook->version }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge badge-{{ $handbook->status === 'published' ? 'success' : 'warning' }}">
                                            {{ ucfirst($handbook->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $handbook->description ?: 'No description provided.' }}</td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td>{{ $handbook->department->name ?? 'All Departments' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $handbook->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $handbook->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h5>Actions</h5>
                            <a href="{{ Storage::url($handbook->file_path) }}" target="_blank" class="btn btn-primary btn-block">
                                <i class="fas fa-download"></i> Download/View File
                            </a>
                            @if(!$acknowledged)
                                <form action="{{ route('handbooks.acknowledge', $handbook) }}" method="POST" style="margin-top: 10px;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-success" style="margin-top: 10px;">
                                    <i class="fas fa-check-circle"></i> You have acknowledged this handbook.
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(Auth::user()->hasRole(['admin', 'company_admin']))
                        <hr>
                        <h5 class="mt-4">Acknowledgments</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Acknowledged At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($handbook->acknowledgments as $acknowledgment)
                                        <tr>
                                            <td>{{ $acknowledgment->user->name }}</td>
                                            <td>{{ $acknowledgment->acknowledged_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No acknowledgments yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection