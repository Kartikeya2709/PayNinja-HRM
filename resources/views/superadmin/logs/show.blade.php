@extends('layouts.app')

@section('title', 'Log: ' . $filename)

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Log: {{ $filename }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('superadmin.logs.index') }}">Logs</a></div>
                <div class="breadcrumb-item">{{ $filename }}</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Log Content</h2>
            <p class="section-lead mt-2">Viewing log file: {{ $filename }} (Total lines: {{ $totalLines }})</p>

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Log Entries</h4>
                    <div class="card-header-action">
                        <a href="{{ route('superadmin.logs.index') }}" class="btn btn-secondary">Back to Logs</a>
                        <a href="{{ route('superadmin.logs.download', $filename) }}" class="btn btn-success">Download File</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($paginatedLines) > 0)
                    <div class="log-content">
                        @foreach($paginatedLines as $line)
                        <div class="log-line">
                            <pre class="mb-0">{{ $line }}</pre>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <nav aria-label="Log pagination">
                            <ul class="pagination">
                                @if($page > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}">Previous</a>
                                </li>
                                @endif

                                @php
                                    $totalPages = ceil($totalLines / $perPage);
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                @endphp

                                @for($i = $startPage; $i <= $endPage; $i++)
                                <li class="page-item {{ $i == $page ? 'active' : '' }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                </li>
                                @endfor

                                @if($page < $totalPages)
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}">Next</a>
                                </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                    @else
                    <p class="text-center text-muted">No log entries found.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.log-content {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    line-height: 1.4;
    max-height: 600px;
    overflow-y: auto;
}

.log-line {
    border-bottom: 1px solid #e9ecef;
    padding: 0.25rem 0;
}

.log-line:last-child {
    border-bottom: none;
}

.log-line pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
    color: #495057;
}
</style>
@endsection
