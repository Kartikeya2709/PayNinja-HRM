@extends('layouts.app')

@section('content')
<section class="section container">
        <div class="section-header">
            <h1>Create Announcement</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="">Create Announcement</a></div>
            </div>
        </div>
<div class="card mb-4">
            <div class="card-header justify-content-center mb-2">
                <h5>Create Announcement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('company-admin.announcements.store') }}">
                    @csrf
                    <div class="row">
                         <div class="col-lg-12 mb-3 form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-sm-12 mb-3 form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" required></textarea>
                    </div>
                    <div class="col-lg-6 col-sm-12 mb-3 form-group">
                        <label for="audience" class="form-label">Audience</label>
                        <select name="audience" id="audience" class="form-control" required>
                            <option value="employees">Employees</option>
                            <option value="admins">Admins</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-sm-12 mb-3 form-group">
                        <label for="publish_date" class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" id="publish_date" class="form-control">
                    </div>
                    <div class="col-lg-6 col-sm-12 mb-3 form-group">
                        <label for="expires_at" class="form-label">Expires At</label>
                        <input type="date" name="expires_at" id="expires_at" class="form-control">
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                         <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                         <i class="bi bi-save me-2"></i>Create Announcement
                         </button>
                    </div>

                </form>
            </div>
        </div>
        </section>
    

@endsection