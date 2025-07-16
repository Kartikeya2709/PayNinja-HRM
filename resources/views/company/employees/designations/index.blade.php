@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Designations</h3>
                    <div class="card-tools">
                        <a href="{{ route('company.designations.create') }}" class="btn btn-primary">
                            Add New Designation
                        </a>
                    </div>
                </div>
                <div class="card-body Designations-table">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Level</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($designations as $designation)
                                <tr>
                                    <td>{{ $designation->title }}</td>
                                    <td>{{ $designation->level }}</td>
                                    <td>{{ $designation->description }}</td>
                                    <td>
                                        <a href="{{ route('company.designations.edit', $designation) }}" class="btn btn-sm btn-info">
                                            Edit
                                        </a>
                                        <form action="{{ route('company.designations.destroy', $designation) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this designation?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
