@extends('layouts.app')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Edit Announcement</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Edit Announcement</a></div>
            </div>
        </div>
<div class="row">
	<div class="col-lg-12 mx-auto">
		<div class="card">
			<div class="card-header justify-content-center mb-2">
				<h5>Edit Announcement</h5>
			</div>
			<div class="card-body">
				<form method="POST" action="{{ route('announcements.update', \Illuminate\Support\Facades\Crypt::encrypt($announcement->id)) }}">
					@csrf
					@method('PUT')
					<div class="row">
						<div class="col-md-6 col-sm-6">
					<div class="mb-3 form-group">
						<label for="title" class="form-label">Title</label>
						<input type="text" name="title" id="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
					</div>
					</div>
					<div class="col-md-6 col-sm-6">
					<div class="mb-3 form-group">
						<label for="description" class="form-label">Description</label>
						<textarea name="description" id="description" class="form-control" required>{{ old('description', $announcement->description) }}</textarea>
					</div>
					</div>
					</div>
					<div class="row">
						<div class="col-md-6 col-sm-6">
					<div class="mb-3 form-group">

						<label for="expires_at" class="form-label">Expires At</label>
						<input type="date" name="expires_at" id="expires_at" class="form-control" value="{{ old('expires_at', $announcement->expires_at ? \Carbon\Carbon::parse($announcement->expires_at)->format('Y-m-d') : '') }}">
					</div>
					</div>
					<div class="col-md-6 col-sm-6">
					<div class="mb-3 form-group">
						<label for="publish_date" class="form-label">Publish Date</label>
						<input type="date" name="publish_date" id="publish_date" class="form-control" value="{{ old('publish_date', $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d') : '') }}">
					</div>
					</div>
					</div>
					<div class="row">
					<div class="mb-3 form-group">
						<label for="audience" class="form-label">Audience</label>
						<select name="audience" id="audience" class="form-control" required>
							<option value="employees" {{ $announcement->audience == 'employees' ? 'selected' : '' }}>Employees</option>
							<option value="admins" {{ $announcement->audience == 'admins' ? 'selected' : '' }}>Admins</option>
							<option value="both" {{ $announcement->audience == 'both' ? 'selected' : '' }}>Both</option>
						</select>
					</div>
					</div>
					<div class="d-flex gap-3 justify-content-center mt-4">
                       <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                           <i class="bi bi-save me-2"></i>Update Announcement
                       </button>
                           <a href="{{ route('announcements.index') }}" class="btn btn-danger px-4 rounded-pill">
                           <i class="bi bi-x-circle me-2"></i>Back to List
                           </a>
                    </div>

				</form>
			</div>
		</div>
	</div>
</div>
</section>
</div>
@endsection
