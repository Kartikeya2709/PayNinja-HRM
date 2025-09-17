@extends('layouts.app')

@section('content')
<div class="row mt-4">
	<div class="col-lg-8 mx-auto">
		<div class="card">
			<div class="card-header">
				<h5>Announcement Details</h5>
			</div>
			<div class="card-body">
				<dl class="row mb-0">
					<dt class="col-sm-4">Title</dt>
					<dd class="col-sm-8">{{ $announcement->title }}</dd>

					<dt class="col-sm-4">Description</dt>
					<dd class="col-sm-8">{{ $announcement->description }}</dd>

					<dt class="col-sm-4">Audience</dt>
					<dd class="col-sm-8">{{ ucfirst($announcement->audience) }}</dd>

					<dt class="col-sm-4">Publish Date</dt>
					<dd class="col-sm-8">{{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d') : '-' }}</dd>

					<dt class="col-sm-4">Expires At</dt>
					<dd class="col-sm-8">{{ $announcement->expires_at ? \Carbon\Carbon::parse($announcement->expires_at)->format('Y-m-d') : '-' }}</dd>

					<dt class="col-sm-4">Created By</dt>
					<dd class="col-sm-8">{{ $announcement->creator->name ?? '-' }}</dd>

					<dt class="col-sm-4">Created At</dt>
					<dd class="col-sm-8">{{ $announcement->created_at->format('Y-m-d H:i') }}</dd>
				</dl>
				<a href="{{ route('company-admin.announcements.index') }}" class="btn btn-secondary mt-3">Back to List</a>
				<a href="{{ route('company-admin.announcements.edit', $announcement->id) }}" class="btn btn-primary mt-3 ms-2">Edit</a>
			</div>
		</div>
	</div>
</div>
@endsection
