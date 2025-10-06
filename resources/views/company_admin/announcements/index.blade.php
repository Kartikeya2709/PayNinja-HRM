@extends('layouts.app')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Announcement</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Announcement</a></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 px-1 cash-dep">
                <div class="card">
                    <div class="card-header">
                        <h5>Announcement List</h5>
                    </div>
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
                                        <td>{{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td>
                                            @php
                                            $now = \Carbon\Carbon::now();
                                            if ($announcement->publish_date && $now->lt($announcement->publish_date)) {
                                            $status = ['Upcoming', 'info'];
                                            } elseif ($announcement->expires_at && $now->gt($announcement->expires_at))
                                            {
                                            $status = ['Completed', 'success'];
                                            } else {
                                            $status = ['Ongoing', 'warning'];
                                            }
                                            @endphp
                                            <span class="badge bg-{{ $status[1] }}">{{ $status[0] }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                            <a href="{{ route('company-admin.announcements.show', $announcement->id) }}"
                                                class="btn btn-outline-info btn-sm action-btn"
                                                data-id="{{ $announcement->id }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Show Announcement" aria-label="Show">
                                                <span class="btn-content">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                    aria-hidden="true"></span>
                                            </a>

                                            @if(auth()->id() === $announcement->created_by ||
                                            auth()->user()->hasRole('company_admin'))
                                            <a href="{{ route('company-admin.announcements.edit', $announcement->id) }}"
                                                class="btn btn-outline-primary btn-sm action-btn"
                                                data-id="{{ $announcement->id }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Edit Announcement" aria-label="Edit">
                                                <span class="btn-content">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                                    aria-hidden="true"></span>
                                            </a>

                                            <form
                                                action="{{ route('company-admin.announcements.destroy', $announcement->id) }}"
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-outline-danger btn-sm action-btn rounded-start-0"
                                                    data-id="{{ $announcement->id }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Delete Announcement"
                                                    aria-label="Delete"
                                                    onclick="return confirm('Delete this announcement?')">
                                                    <span class="btn-content">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                                        aria-hidden="true"></span>
                                                </button>

                                            </form>
                                            </div>
                                            @endif
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
    </section>
</div>

@endsection