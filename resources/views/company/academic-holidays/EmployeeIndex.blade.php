@extends('layouts.app')

@section('title', 'Company Holidays')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Company Holidays</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Company Holidays</div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Academic Holidays</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success mb-2">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Holiday Name</th>
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>Duration</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($companyHolidays as $holiday)
                                    <tr>
                                        <td>{{ $holiday->name }}</td>
                                        <td>{{ $holiday->from_date ? \Carbon\Carbon::parse($holiday->from_date)->format('M d, Y') : '-' }}</td>
                                        <td>{{ $holiday->to_date ? \Carbon\Carbon::parse($holiday->to_date)->format('M d, Y') : '-' }}</td>
                                        <td>
                                            @if($holiday->from_date && $holiday->to_date)
                                                {{ \Carbon\Carbon::parse($holiday->from_date)->diffInDays(\Carbon\Carbon::parse($holiday->to_date)) + 1 }} day(s)
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $holiday->description ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No holidays found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            @if($companyHolidays->count() > 0)
                            <div class="mt-3">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Total Holidays:</strong> {{ $companyHolidays->count() }} holidays listed
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
