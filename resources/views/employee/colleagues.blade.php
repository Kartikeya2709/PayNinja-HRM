@extends('layouts.app')

@section('title', 'My Colleagues')

@push('style')
    <!-- Add any specific CSS if needed -->
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    <style>
        .card-header h4 {
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <section class="section">
            <div class="section-header">
                <h1>Colleagues at {{ $companyName ?? 'Your Company' }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">My Colleagues</div>
                </div>
            </div>

            <div class="section-body">
                @if ($currentUser && $currentUser->company_id)
                    <div class="card">
                        <div class="card-header">
                            <h4>Employee List</h4>
                        </div>
                        <div class="card-body">
                            @if ($colleagues->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped" id="colleaguesTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                {{-- Add more columns as needed --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($colleagues as $index => $colleague)
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td>{{ $colleague->name }}</td>
                                                    <td>{{ $colleague->email }}</td>
                                                    <td>{{ ucfirst($colleague->role) }}</td>
                                                    {{-- Display other relevant colleague info --}}
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No colleagues found in the same company.
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        You are not currently associated with a company, or your company ID is missing.
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

    <!-- Page Specific JS File -->
    <script>
        $(document).ready(function() {
            $('#colleaguesTable').DataTable({
                "columnDefs": [
                    { "sortable": false, "targets": [0] } // Example: disable sorting on first column
                ],
                "pageLength": 10,
            });
        });
    </script>
@endpush
