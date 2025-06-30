@extends('layouts.app')

@section('title', 'Dashboard')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card-icon {
            font-size: 2.5rem;
            /* Increased icon size */
            opacity: 0.3;
            position: absolute;
            right: 35px;
            /* Adjusted position */
            top: 50%;
            transform: translateY(-50%);
        }

        .card.card-statistic-1 .card-header h4 {
            font-size: 1rem;
            /* Adjusted for consistency */
        }

        .card.card-statistic-1 .card-body {
            font-size: 2rem;
            /* Increased data font size */
            font-weight: 600;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard</h1>
                @if ($loggedInUser)
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item active">Welcome, {{ $loggedInUser->name }}!</div>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 card-hover">
                        <div class="card-icon text-white">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Companies</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalCompanies ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 card-hover">
                        <div class="card-icon text-white">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Employees</h4>
                            </div>
                            <div class="card-body">
                                {{ ($usersByRole['employee'] ?? 0) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 card-hover">
                        <div class="card-icon text-white">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Departments</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalDepartments ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 card-hover">
                        <div class="card-icon text-white">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Super Admins</h4>
                            </div>
                            <div class="card-body">
                                {{ $usersByRole['superadmin'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 card-hover">
                        <div class="card-icon text-white">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Admins</h4>
                            </div>
                            <div class="card-body">
                                {{ $usersByRole['admin'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 card-hover">
                        <div class="card-icon text-white">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Regular Users</h4>
                            </div>
                            <div class="card-body">
                                {{ $usersByRole['user'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Add more cards here for other roles if needed --}}
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>User Roles Distribution</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="userRolesPieChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>Companies and Admins</h4>
                        </div>
                        <div class="card-body card-body-box">
                            @if ($companiesWithAdmins && $companiesWithAdmins->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Company Name</th>
                                                <th>Admin Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($companiesWithAdmins as $company)
                                                <tr>
                                                    <td>{{ $company->name }}</td>
                                                    <td>{{ $company->admin ? $company->admin->name : 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p>No companies found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> {{-- Added Chart.js CDN --}}

    <!-- Page Specific JS File -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('userRolesPieChart').getContext('2d');
            var userRolesPieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Super Admins', 'Admins', 'Regular Users'],
                    datasets: [{
                        label: 'User Roles',
                        data: [
                            {{ $usersByRole['superadmin'] ?? 0 }},
                            {{ $usersByRole['admin'] ?? 0 }},
                            {{ $usersByRole['user'] ?? ($usersByRole['employee'] ?? 0) }}
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)', // Blue
                            'rgba(255, 206, 86, 0.7)', // Yellow
                            'rgba(75, 192, 192, 0.7)' // Green
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'top',
                    }
                }
            });
        });
    </script>
@endpush
