@extends('layouts.app')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Announcement</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ route('home') }}">Dashboard</a>
                </div>
                <div class="breadcrumb-item">Announcement</div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 px-1 cash-dep">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center mb-3">
                        <h5>Announcement List</h5>
                        @if(\App\Models\User::hasAccess('announcement-create', true))
                        <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Announcement
                        </a>
                        @endif
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
                                         @if(\App\Models\User::hasAccess('announcement-show/{announcement}', true) ||
                                             \App\Models\User::hasAccess('announcement-edit/{announcement}', true) ||
                                             \App\Models\User::hasAccess('announcement-delete/{announcement}', true))
                                        <th>Action</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($announcements as $i => $announcement)
                                    @php
                                        $encryptedId = encrypt($announcement->id);
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $announcement->title }}</td>
                                        <td>{{ $announcement->description }}</td>
                                        <td>
                                            {{ $announcement->publish_date
                                                ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d')
                                                : '-' }}
                                        </td>
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
                                             @if(\App\Models\User::hasAccess('announcement-show/{announcement}', true) ||
                                             \App\Models\User::hasAccess('announcement-edit/{announcement}', true) ||
                                             \App\Models\User::hasAccess('announcement-delete/{announcement}', true))
                                        
                                       
                                        <td>
                                            <div class="btn-group btn-group-sm">

                                                {{-- SHOW --}}
                                                @if(\App\Models\User::hasAccess('announcement-show/{announcement}', true))
                                                <a href="{{ route('announcements.show', $encryptedId) }}"
                                                   class="btn btn-outline-info btn-sm action-btn"
                                                   title="Show Announcement">
                                                   <i class="fas fa-eye"></i>
                                                </a>
                                                @endif

                                                {{-- @if(auth()->id() === $announcement->created_by || auth()->user()->hasRole('company_admin')) --}}
                                                    @if(\App\Models\User::hasAccess('announcement-edit/{announcement}', true))
                                                        {{-- EDIT --}}
                                                        <a href="{{ route('announcements.edit', $encryptedId) }}"
                                                           class="btn btn-outline-primary btn-sm action-btn"
                                                           title="Edit Announcement">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    @if(\App\Models\User::hasAccess('announcement-delete/{announcement}', true))
                                                        {{-- DELETE --}}
                                                        <form action="{{ route('announcements.destroy', $encryptedId) }}"
                                                              method="POST"
                                                              style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-outline-danger btn-sm rounded-start-0 action-btn"
                                                                    title="Delete Announcement"
                                                                    onclick="return confirm('Delete this announcement?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                {{-- @endif --}}

                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No announcements found.</td>
                                    </tr>
                                    @endforelse
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
