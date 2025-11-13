@extends('layouts.app')

@section('content')
<div class="container-fluid">

      <section class="section">
    <div class="section-header">
            <h1>HR Handbooks</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">HR Handbooks</a></div>
            </div>
        </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-center">
                    <h4 class="mb-3">HR Handbooks</h4>
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
                                            {{-- <a href="{{ route('handbooks.show', $handbook) }}"> --}}
                                                {{ $handbook->title }}
                                            {{-- </a> --}}
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
                                                <div class="btn-group btn-group-sm">

                                                <!-- View Button -->
                                                <a href="{{ route('handbooks.show', $handbook) }}"
                                                   class="btn btn-outline-info btn-sm action-btn"
                                                   data-id="{{ $handbook->id }}" data-bs-toggle="tooltip"
                                                   data-bs-placement="top" title="View Handbook" aria-label="View">
                                                   <span class="btn-content">
                                                   <i class="fas fa-eye"></i>
                                                   </span>
                                                   <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                </a>

                                                <!-- Edit Button -->
                                                <a href="{{ route('handbooks.edit', $handbook) }}"
                                                   class="btn btn-outline-primary btn-sm action-btn"
                                                   data-id="{{ $handbook->id }}" data-bs-toggle="tooltip"
                                                   data-bs-placement="top" title="Edit Handbook" aria-label="Edit">
                                                   <span class="btn-content">
                                                   <i class="fas fa-edit"></i>
                                                   </span>
                                                   <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                </a>

                                                <!-- Delete Button -->
                                                <form action="{{ route('handbooks.destroy', $handbook) }}" method="POST" class="d-inline-block">
                                                   @csrf
                                                   @method('DELETE')
                                                   <button type="submit"
                                                   class="btn btn-outline-danger btn-sm action-btn rounded-start-0"
                                                   data-id="{{ $handbook->id }}" data-bs-toggle="tooltip"
                                                   data-bs-placement="top" title="Delete Handbook" aria-label="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this handbook?')">
                                                   <span class="btn-content">
                                                   <i class="fas fa-trash"></i>
                                                   </span>
                                                   <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                   </button>
                                                </form>
                                                </div>

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