@extends('layouts.app')

@section('content')
	<section class="section container">
		<div class="section-header">
			<h1>Announcement Details</h1>
			<div class="section-header-breadcrumb">
				<div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
				<div class="breadcrumb-item"><a href="">Announcement Details</a></div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 mx-auto">
				<div class="announcement-card shadow-lg rounded-4 p-4 bg-white">
                   <div class="announcement-info-card">
                      <div class="accent-bar mb-3"></div>
                         <h5 class="fw-bold text-dark mb-4">
                         <i class="fas fa-bullhorn me-2 text-primary"></i>Announcement Details
                         </h5>

                   <div class="info-grid">
                      <div><span>Title:</span> {{ $announcement->title }}</div>
                      <div><span>Description:</span> {{ $announcement->description }}</div>
                      <div><span>Audience:</span> {{ ucfirst($announcement->audience) }}</div>
                      <div><span>Publish Date:</span>
                      {{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d') : '-' }}
                      </div>
                      <div><span>Expires At:</span>
                      {{ $announcement->expires_at ? \Carbon\Carbon::parse($announcement->expires_at)->format('Y-m-d') : '-' }}
                      </div>
                      <div><span>Created By:</span> {{ $announcement->creator->name ?? '-' }}</div>
                      <div><span>Created At:</span> {{ $announcement->created_at->format('Y-m-d H:i') }}</div>
                      </div>

                      <div class="mt-4">
                         <a href="{{ url()->previous() }}" class="btn btn-secondary">
                         <i class="fas fa-arrow-left me-1"></i> Back
                         </a>
                         @if(auth()->id() === $announcement->created_by || auth()->user()->hasRole('company_admin'))
                         <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-primary ms-2">
                         <i class="fas fa-edit me-1"></i> Edit
                         </a>
                         @endif
                      </div>
                   </div>
                </div>
			</div>
		</div>
	</div>
</section>
@endsection