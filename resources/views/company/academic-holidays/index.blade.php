@extends('layouts.app')

@section('title', 'Academic Holidays')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Academic Holidays</h3>
                    <div class="academic d-flex align-items-center gap-2">
                        <a href="{{ route('company.academic-holidays.create', $company->id) }}" class="btn btn-primary d-flex align-items-center gap-1">
                            <i class="fas fa-plus"></i> Add Holiday
                        </a>                        <button type="button" class="btn btn-success d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import"></i> Import Holidays
                        </button>
                        <a href="{{ route('company.academic-holidays.template', $company->id) }}" class="btn btn-info d-flex align-items-center gap-1">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($holidays as $holiday)
                                <tr>                                        <td>{{ $holiday->name }}</td>
                                        <td>{{ $holiday->from_date ? \Carbon\Carbon::parse($holiday->from_date)->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $holiday->to_date ? \Carbon\Carbon::parse($holiday->to_date)->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $holiday->description ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('company.academic-holidays.edit', [$company->id, $holiday->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('company.academic-holidays.destroy', [$company->id, $holiday->id]) }}" 
                                              method="POST" 
                                              class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this holiday?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No holidays found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Academic Holidays</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('company.academic-holidays.import', $company->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Select Excel File</label>
                        <input type="file" class="form-control" id="file" name="file" required accept=".xlsx,.xls">
                        <div class="form-text">Only Excel files (.xlsx, .xls) are allowed.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
