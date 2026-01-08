@extends('layouts.app')

@section('content')
<div class="container-fluid">

      <section class="section">
    <div class="section-header">
            <h1>Employee Handbooks</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Employee Handbooks</div>
            </div>
        </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-center">
                    <h4 class="mb-3">Company Handbooks & Policies</h4>
                </div>
                <div class="card-body">
                    @if($handbooks->count() > 0)
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
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($handbooks as $handbook)
                                        <tr>
                                              <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <a href="{{ route('handbooks.show', Crypt::encrypt($handbook->id)) }}">
                                                    {{ $handbook->title }}
                                                </a>
                                                @if($handbook->file_path)
                                                    <a href="{{ route('handbooks.download', Crypt::encrypt($handbook->id)) }}"
                                                       class="btn btn-sm btn-outline-primary ms-2"
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="Download Handbook PDF">
                                                       <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
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
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <!-- View Button -->
                                                    <a href="{{ route('handbooks.show', Crypt::encrypt($handbook->id)) }}"
                                                       class="btn btn-outline-info btn-sm action-btn"
                                                       data-id="{{ $handbook->id }}" data-bs-toggle="tooltip"
                                                       data-bs-placement="top" title="View Handbook" aria-label="View">
                                                       <span class="btn-content">
                                                       <i class="fas fa-eye"></i>
                                                       </span>
                                                       <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                    </a>

                                                    <!-- Acknowledge Button -->
                                                    @if(!$handbook->isAcknowledgedBy(Auth::user()))
                                                        <form action="{{ route('handbooks.acknowledge', Crypt::encrypt($handbook->id)) }}" method="POST" class="d-inline-block">
                                                           @csrf
                                                           <button type="submit"
                                                           class="btn btn-outline-success btn-sm action-btn"
                                                           data-id="{{ $handbook->id }}" data-bs-toggle="tooltip"
                                                           data-bs-placement="top" title="Acknowledge Handbook" aria-label="Acknowledge"
                                                           onclick="return confirm('Are you sure you want to acknowledge this handbook?')">
                                                           <span class="btn-content">
                                                           <i class="fas fa-check"></i>
                                                           </span>
                                                           <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                           </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-success btn-sm" disabled title="Already Acknowledged">
                                                            <i class="fas fa-check-circle"></i> Acknowledged
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> No handbooks available for your department at the moment.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $handbooks->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h5>No Handbooks Available</h5>
                            <p>There are currently no handbooks available for your department. Please check back later or contact your administrator.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
</div>
@endsection
