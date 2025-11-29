@extends('layouts.app')

@section('title', 'Table: ' . $table)

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Table: {{ $table }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('superadmin.database.index') }}">Database</a></div>
                <div class="breadcrumb-item">{{ $table }}</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Table Data</h2>
            <p class="section-lead mt-2">Viewing data from table: {{ $table }} (Total records: {{ $totalRecords }})</p>

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Query Section -->
            <div class="collapse" id="querySection">
                <div class="card">
                    <div class="card-header">
                        <h4>Execute Custom Query</h4>
                        <div class="card-header-action">
                            <button class="btn btn-secondary" type="button" onclick="toggleQuery()">
                                <i class="fas fa-times"></i> Close
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('superadmin.database.show', $table) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="query">SQL Query (SELECT only)</label>
                                <textarea name="query" id="query" class="form-control" rows="6" placeholder="Enter your SELECT query here...

Example: SELECT * FROM {{ $table }} WHERE id > 10 LIMIT 20;" required>{{ old('query', $query ?? 'SELECT * FROM ' . $table . ' LIMIT 50;') }}</textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Execute Query
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" onclick="clearQuery()">
                                    <i class="fas fa-eraser"></i> Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            @if(!isset($queryResults) || !$isSelect)
            <div class="card">
                <div class="card-header">
                    <h4>Records</h4>
                    <div class="card-header-action">
                        <form method="GET" action="{{ route('superadmin.database.show', $table) }}" class="d-inline">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search records..." value="{{ $search ?? '' }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(isset($search) && $search)
                                        <a href="{{ route('superadmin.database.show', $table) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        <button class="btn btn-info ml-2" type="button" onclick="toggleQuery()">
                            <i class="fas fa-terminal"></i> <span id="queryText">Run Query</span>
                        </button>
                        <a href="{{ route('superadmin.database.export-table', $table) }}" class="btn btn-success ml-2">
                            <i class="fas fa-download"></i> Export Table
                        </a>
                        <a href="{{ route('superadmin.database.index') }}" class="btn btn-secondary ml-2">Back to Tables</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($data->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    @foreach($columns as $column)
                                    <th>{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $row)
                                <tr>
                                    @foreach($columns as $column)
                                    <td>
                                        @if(is_null($row->$column))
                                            <em class="text-muted">NULL</em>
                                        @elseif(is_array($row->$column) || is_object($row->$column))
                                            <pre class="small">{{ json_encode($row->$column, JSON_PRETTY_PRINT) }}</pre>
                                        @else
                                            {{ Str::limit($row->$column, 100) }}
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $data->links() }}
                    </div>
                    @else
                    <p class="text-center text-muted">No records found in this table.</p>
                    @endif
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-header">
                    <h4>Query Results</h4>
                    <div class="card-header-action">
                        <button class="btn btn-info" type="button" onclick="toggleQuery()">
                            <i class="fas fa-terminal"></i> <span id="queryText">Run Query</span>
                        </button>
                        <a href="{{ route('superadmin.database.show', $table) }}?clear_query=1" class="btn btn-secondary ml-2">
                            <i class="fas fa-table"></i> Back to Table View
                        </a>
                        <a href="{{ route('superadmin.database.index') }}" class="btn btn-secondary ml-2">Back to Tables</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Query executed:</strong> {{ $query }}
                    </div>
                    @if($affectedRows > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    @if($queryResults->count() > 0)
                                        @foreach(array_keys((array)$queryResults->first()) as $column)
                                        <th>{{ $column }}</th>
                                        @endforeach
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($queryResults as $row)
                                <tr>
                                    @foreach((array)$row as $value)
                                    <td>
                                        @if(is_null($value))
                                            <em class="text-muted">NULL</em>
                                        @elseif(is_array($value) || is_object($value))
                                            <pre class="small">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                        @else
                                            {{ Str::limit($value, 100) }}
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $queryResults->links() }}
                    </div>
                    @else
                    <p class="text-center text-muted">No results found.</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </section>
</div>

<script>
function toggleQuery() {
    const querySection = document.getElementById('querySection');
    const queryText = document.getElementById('queryText');

    if (querySection.classList.contains('show')) {
        querySection.classList.remove('show');
        queryText.textContent = 'Run Query';
    } else {
        querySection.classList.add('show');
        queryText.textContent = 'Hide Query';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textarea = document.getElementById('query');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Initialize textarea height
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
});

// Clear query function
function clearQuery() {
    const textarea = document.getElementById('query');
    if (textarea) {
        textarea.value = 'SELECT * FROM {{ $table }} LIMIT 50;';
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
        textarea.focus();
    }
}
</script>
@endsection
