@extends('layouts.app')

@section('title', 'Manage Teams')

@section('content')
<div class="container">
<div class="section-header">
    <h1>Manage Teams</h1>
</div>

<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Teams</h3>
                    <div class="card-tools">
                        <a href="{{ route('company.teams.create', ['companyId' => Auth::user()->company_id]) }}" class="btn btn-primary">
                            Create New Team
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Team Name</th>
                                    <th>Department</th>
                                    <th>Reporter</th>
                                    <th>Reportees</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($teams as $team)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $team->name }}</td>
                                        <td>{{ $team->department->name }}</td>
                                        <td>
                                            @foreach($team->reporters as $reporter)
                                                {{ $reporter->name }}
                                            @endforeach
                                        </td>
                                        <td>
                                            <ul class="list-unstyled">
                                                @foreach($team->reportees as $reportee)
                                                    <li>{{ $reportee->name }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>
                                            <a href="{{ route('company.teams.edit', ['companyId' => Auth::user()->company_id, 'team' => $team]) }}" class="btn btn-sm btn-info">
                                                Edit
                                            </a>
                                            <form action="{{ route('company.teams.destroy', ['companyId' => Auth::user()->company_id, 'team' => $team]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this team?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No teams found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
