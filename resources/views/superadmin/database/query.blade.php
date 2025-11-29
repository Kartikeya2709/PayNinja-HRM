@extends('layouts.app')

@section('title', 'SQL Query')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>SQL Query</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('superadmin.database.index') }}">Database</a></div>
                <div class="breadcrumb-item">SQL Query</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Execute SQL Queries</h2>
            <p class="section-lead mt-2">Run SELECT queries to view data or other SQL statements to modify the database.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Query Editor</h4>
                    <div class="card-header-action">
                        <a href="{{ route('superadmin.database.index') }}" class="btn btn-secondary">Back to Tables</a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('superadmin.database.execute-query') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="query">SQL Query</label>
                            <textarea name="query" id="query" class="form-control" rows="12" placeholder="Enter your SQL query here...

Common Examples:
• SELECT * FROM users LIMIT 10;
• SHOW TABLES;
• DESCRIBE users;
• SELECT COUNT(*) FROM users;
• SELECT * FROM users WHERE created_at >= '2024-01-01';" required>{{ old('query', $preFilledQuery ?? $query ?? '') }}</textarea>
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

            @if(isset($results) && $isSelect)
            <div class="card mt-4">
                <div class="card-header">
                    <h4>Query Results ({{ $affectedRows }} rows)</h4>
                </div>
                <div class="card-body">
                    @if($affectedRows > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    @if(count($results) > 0)
                                        @foreach(array_keys((array)$results[0]) as $column)
                                        <th>{{ $column }}</th>
                                        @endforeach
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $row)
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
                    @else
                    <p class="text-center text-muted">No results found.</p>
                    @endif
                </div>
            </div>
            @elseif(isset($affectedRows) && !$isSelect)
            <div class="card mt-4">
                <div class="card-header">
                    <h4>Query Result</h4>
                </div>
                <div class="card-body">
                    <p class="text-success">Query executed successfully. Affected rows: {{ $affectedRows }}</p>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textarea = document.getElementById('query');
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });

    // Initialize textarea height
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
});

// Clear query function
function clearQuery() {
    const textarea = document.getElementById('query');
    textarea.value = '';
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
    textarea.focus();
}
</script>
@endsection
