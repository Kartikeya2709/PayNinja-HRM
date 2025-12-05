@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('check-in-out') ? 'active' : '' }}" href="{{ route('check-in-out') }}">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Check In/Out
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('my-attendance*') ? 'active' : '' }}" href="{{ route('my-attendance') }}">
                            <i class="bi bi-calendar-check me-2"></i> My Attendance
                        </a>
                    </li>
                    @can('view attendance')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin-attendance.*') ? 'active' : '' }}" href="{{ route('admin-attendance.index') }}">
                            <i class="bi bi-people me-2"></i> Manage Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}" href="{{ route('admin.shifts.index') }}">
                            <i class="bi bi-clock-history me-2"></i> Manage Shifts
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">@yield('title')</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    @yield('header-buttons')
                </div>
            </div>

            @includeIf('partials.alerts')
            
            @yield('content')
        </main>
    </div>
</div>
@endsection
