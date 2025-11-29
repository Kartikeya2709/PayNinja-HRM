@extends('layouts.app')

@section('title', 'Database Management')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Database Management</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Database</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Database Tables</h2>
            <p class="section-lead mt-2">Browse and manage database tables and data.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Summary Cards and Search -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <div class="card-icon mb-3">
                                <i class="fas fa-database fa-2x text-primary"></i>
                            </div>
                            <h4 class="card-title">{{ $totalTables }} @if($search)<small class="text-muted">/ {{ $allTablesCount }}</small>@endif</h4>
                            <p class="card-text text-muted mb-3">Tables{{ $search ? ' (Filtered)' : '' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <div class="card-icon mb-3">
                                <i class="fas fa-table fa-2x text-success"></i>
                            </div>
                            <h4 class="card-title">{{ number_format($totalRecords) }} @if($search)<small class="text-muted">/ {{ number_format($allRecordsCount) }}</small>@endif</h4>
                            <p class="card-text text-muted mb-3">Records{{ $search ? ' (Filtered)' : '' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <div class="card-icon mb-3">
                                <i class="fas fa-code fa-2x text-info"></i>
                            </div>
                            <h4 class="card-title">SQL</h4>
                            <p class="card-text text-muted mb-3">Query Tool</p>
                            <a href="{{ route('superadmin.database.query') }}" class="btn btn-info btn-sm">Execute Query</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <div class="card-icon mb-3">
                                <i class="fas fa-search fa-2x text-warning"></i>
                            </div>
                            <h4 class="card-title">Search</h4>
                            <p class="card-text text-muted mb-3">Table Filter</p>
                            <button class="btn btn-warning btn-sm" type="button" onclick="toggleSearch()">
                                <i class="fas fa-search"></i> <span id="searchText">Search Tables</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Form -->
            <div class="collapse" id="searchSection">
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('superadmin.database.index') }}">
                            <div class="form-row align-items-center">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="Search table names..." value="{{ $search ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    @if(isset($search) && $search)
                                        <a href="{{ route('superadmin.database.index') }}" class="btn btn-outline-secondary ml-2">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Database Tables @if($search)(Filtered: {{ $totalTables }} tables, {{ number_format($totalRecords) }} records) @else ({{ $totalTables }} tables, {{ number_format($totalRecords) }} total records)@endif</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Table Name</th>
                                    <th>Records</th>
                                    <th>Columns</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tableInfo as $index => $table)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $table['name'] }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">{{ number_format($table['record_count']) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $table['column_count'] }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('superadmin.database.show', $table['name']) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('superadmin.database.export-table', $table['name']) }}" class="btn btn-success btn-sm ml-1">
                                            <i class="fas fa-download"></i> Export
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No tables found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function toggleSearch() {
    const searchSection = document.getElementById('searchSection');
    const searchText = document.getElementById('searchText');

    if (searchSection.classList.contains('show')) {
        searchSection.classList.remove('show');
        searchText.textContent = 'Search Tables';
    } else {
        searchSection.classList.add('show');
        searchText.textContent = 'Hide Search';
    }
}
</script>
@endsection
