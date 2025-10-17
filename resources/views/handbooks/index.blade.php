@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>HR Handbooks</h4>
                    @if(Auth::user()->hasRole(['admin', 'company_admin']))
                        <a href="{{ route('handbooks.create') }}" class="btn btn-primary">Create New Handbook</a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Title</th>
                                    <th>Version</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    @if(Auth::user()->hasRole(['admin', 'company_admin']))
                                        <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($handbooks as $handbook) 
                                    <tr>
                                          <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('handbooks.show', $handbook) }}">{{ $handbook->title }}</a>
                                        </td>
                                        <td>{{ $handbook->version }}</td>
                                        <td>{{ $handbook->department->name ?? 'All Departments' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $handbook->status === 'published' ? 'success' : 'warning' }}">
                                                {{ ucfirst($handbook->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $handbook->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $handbook->created_at->format('M d, Y') }}</td>
                                        @if(Auth::user()->hasRole(['admin', 'company_admin']))
                                            <td>
                                                   
                                                    {{-- <a href="{{ route('handbooks.show', $handbook) }}" class="btn btn-sm btn-dark">View</a> --}}
                                            {{-- <a href="{{ route('handbooks.show', $handbook) }}">{{ $handbook->title }}</a> --}}
                                        
                                                <a href="{{ route('handbooks.edit', $handbook) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('handbooks.destroy', $handbook) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </td>
                                          
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->hasRole(['admin', 'company_admin']) ? 7 : 6 }}" class="text-center">
                                            No handbooks found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $handbooks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection