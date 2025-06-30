@extends('layouts.app')
@section('title', 'Company Admin Dashboard')

@push('styles')
<style>
    .card-statistic {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    .card-statistic:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }
    .card-icon {
        font-size: 2.5rem;
        opacity: 0.7;
    }
    .statistic-details {
        border-left: 3px solid #6777ef;
        padding-left: 15px;
    }
    .quick-actions {
        margin-top: 1.5rem;
    }
    .quick-actions .section-title {
        margin-bottom: 1rem;
        font-size: 1.1rem;
        color: #343a40;
    }
    .action-card {
        display: block;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
        border: 1px solid #eef1f6;
        height: 100%;
        text-decoration: none;
        color: #343a40;
    }
    .action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        border-color: #6777ef;
        text-decoration: none;
    }
    .action-card .card-body {
        padding: 1.25rem 0.75rem;
        text-align: center;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .action-icon {
        width: 50px;
        height: 50px;
        margin: 0 auto 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: white;
        font-size: 1.5rem;
        transition: all 0.2s ease;
    }
    .action-card:hover .action-icon {
        transform: scale(1.05);
    }
    .action-card h6 {
        font-weight: 500;
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.2;
    }
    .section-title {
        color: #34395e;
        font-weight: 600;
        position: relative;
        padding-bottom: 10px;
    }
    .section-title:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background: #6777ef;
        border-radius: 3px;
    }
</style>
@endpush

@section('content')
<div class="main-content-01 container">
    <section class="section">
        <div class="section-header">
            <h1>Dashboard Overview</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="http://127.0.0.1:8000/home">Dashboard</a></div>
                <div class="breadcrumb-item">Companies &amp; Users</div>
            </div>
        </div>

        <div class="row">
            <!-- Employees Card -->
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 card-hover">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Employees</h4>
                        </div>
                        <div class="card-body">
                            {{ array_sum($companyRoleData->toArray()) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departments Card -->
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 card-hover">
                    <div class="card-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Departments</h4>
                        </div>
                        <div class="card-body">
                            {{ $departmentCount }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Attendance -->
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1 card-hover">
                    <div class="card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Today's Attendance</h4>
                        </div>
                        <div class="card-body">
                            {{ $todayAttendanceCount }}/{{ $totalEmployees }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="quick-actions">
            <h4 class="section-title">Quick Actions</h4>
            <div class="row g-3">
                <!-- Attendance Card -->
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <a href="{{ route('attendance.dashboard') }}" class="action-card h-100">
                        <div class="card-body p-3">
                            <div class="action-icon">
                                <i class="fas fa-calendar-check"></i>
                            <h6>Attendance</h6>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Departments Card -->
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <a href="{{ route('company.departments.index') }}" class="action-card h-100">
                        <div class="card-body p-3">
                            <div class="action-icon">
                                <i class="fas fa-building"></i>
                            
                            <h6>Departments</h6>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Leave Requests Card -->
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <a href="{{ route('company.leave-requests.index') }}" class="action-card h-100">
                        <div class="card-body p-3">
                            <div class="action-icon">
                                <i class="fas fa-calendar-minus"></i>
                            
                            <h6>Leave Requests</h6>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Employee Management Card -->
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <a href="{{ route('company.employees.index', ['companyId' => auth()->user()->company_id]) }}" class="action-card h-100">
                        <div class="card-body p-3">
                            <div class="action-icon">
                                <i class="fas fa-users"></i>
                           
                            <h6>Employees</h6>
                             </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Employee Distribution Chart
    const employeeCtx = document.getElementById('employeeChart').getContext('2d');
    new Chart(employeeCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($companyRoleLabels) !!},
            datasets: [{
                data: {!! json_encode($companyRoleData) !!},
                backgroundColor: [
                    '#6777ef',
                    '#63ed7a',
                    '#ffa426',
                    '#fc544b',
                    '#3abaf4',
                    '#6554c0',
                    '#ff87a2',
                    '#5d9cec',
                    '#48cfad',
                    '#a389d4'
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
            },
            cutout: '70%',
        }
    });
</script>
@endpush
@endsection
