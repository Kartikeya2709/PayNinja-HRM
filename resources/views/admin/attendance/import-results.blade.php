@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Attendance Import Results</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if (session('import_errors'))
                        <div class="mt-4">
                            <h5>Import Errors</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Row Number</th>
                                            <th>Error Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (session('import_errors') as $error)
                                            <tr>
                                                <td>{{ $error }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('admin-attendance.index') }}" class="btn btn-secondary">Back to Attendance List</a>
                        <a href="{{ route('admin-attendance.import') }}" class="btn btn-primary">Import Another File</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
