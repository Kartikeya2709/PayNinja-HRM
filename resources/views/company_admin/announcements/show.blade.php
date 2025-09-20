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
	<div class="col-lg-8 mx-auto">
		<div class="card">
			<div class="card-header">
				<h5>Announcement Details</h5>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-hover align-middle">
						<tbody>
							<tr>
								<th style="width: 30%;">Title</th>
								<td>{{ $announcement->title }}</td>
							</tr>
							<tr>
								<th>Description</th>
								<td>{{ $announcement->description }}</td>
							</tr>
							<tr>
								<th>Audience</th>
								<td>{{ ucfirst($announcement->audience) }}</td>
							</tr>
							<tr>
								<th>Publish Date</th>
								<td>{{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d') : '-' }}</td>
							</tr>
							<tr>
								<th>Expires At</th>
								<td>{{ $announcement->expires_at ? \Carbon\Carbon::parse($announcement->expires_at)->format('Y-m-d') : '-' }}</td>
							</tr>
							<tr>
								<th>Created By</th>
								<td>{{ $announcement->creator->name ?? '-' }}</td>
							</tr>
							<tr>
								<th>Created At</th>
								<td>{{ $announcement->created_at->format('Y-m-d H:i') }}</td>
							</tr>
						</tbody>
					</table>	
				</div>			
				<a href="{{ route('company-admin.announcements.index') }}" class="btn btn-secondary mt-3">Back</a>
				@if(auth()->id() === $announcement->created_by || auth()->user()->hasRole('company_admin'))
					<a href="{{ route('company-admin.announcements.edit', $announcement->id) }}" class="btn btn-primary mt-3 ms-2">Edit</a>
				@endif
			</div>
		</div>
	</div>
</div>
</section>


@endsection
