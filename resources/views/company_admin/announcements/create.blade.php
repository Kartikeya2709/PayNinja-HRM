@extends('layouts.app')

@section('content')

<div class="card mb-4">
            <div class="card-header">
                <h5>Create Announcement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('company-admin.announcements.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="audience" class="form-label">Audience</label>
                        <select name="audience" id="audience" class="form-control" required>
                            <option value="employees">Employees</option>
                            <option value="admins">Admins</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="publish_date" class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" id="publish_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expires At</label>
                        <input type="date" name="expires_at" id="expires_at" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Announcement</button>
                </form>
            </div>
        </div>

@endsection