@extends('layouts.app')

@section('title', 'Logs Management')

@section('content')
<div class="main-content-01">
    <section class="section container">
        <div class="section-header">
            <h1>Logs Management</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Logs</div>
            </div>
        </div>

        <div class="section-body">
            <h2 class="section-title">Log Files</h2>
            <p class="section-lead mt-2">View and download application log files.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>All Log Files</h4>
                </div>
                <div class="card-body">
                    @if(count($logFiles) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Last Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logFiles as $file)
                                <tr>
                                    <td>{{ $file }}</td>
                                    <td>{{ number_format(filesize(storage_path('logs/' . $file)) / 1024, 2) }} KB</td>
                                    <td>{{ date('Y-m-d H:i:s', filemtime(storage_path('logs/' . $file))) }}</td>
                                    <td>
                                        <a href="{{ route('superadmin.logs.show', $file) }}" class="btn btn-sm btn-primary">View</a>
                                        <a href="{{ route('superadmin.logs.download', $file) }}" class="btn btn-sm btn-success">Download</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-center text-muted">No log files found.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
