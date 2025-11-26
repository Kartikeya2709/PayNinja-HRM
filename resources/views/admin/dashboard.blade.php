@extends('layouts.app')
@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.css">
    <style>
        /* Dashboard Switch Styling */
        .form-check-input:checked {
            background-color: #6777ef;
            border-color: #6777ef;
        }

        .switch-label {
            font-weight: 500;
            color: #6777ef;
            font-size: 0.9rem;
        }

        .form-check-input {
            width: 2.5rem;
            height: 1.25rem;
        }

        .form-check-label {
            margin-left: 0.5rem;
        }
    </style>
    {{-- <style>
        /* Stats Cards */
        .card-statistic-1 {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            border: none;
            overflow: hidden;
            background: #fff;
        }

        .card-statistic-1:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .card-icon {
            font-size: 2.2rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            margin: 20px auto 15px;
            color: white;
        }

        .card-wrap {
            padding: 15px 20px;
            text-align: center;
        }

        .card-header h4 {
            font-size: 1rem;
            color: #6c757d;
            margin: 0 0 5px 0;
            font-weight: 500;
        }

        .card-body {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            padding: 0 0 15px 0;
            line-height: 1.2;
        }

        /* Quick Action Cards */
        .action-card {
            display: block;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.5, 1);
            border: 1px solid #eef1f6;
            color: #2c3e50;
            text-decoration: none;
            margin-bottom: 1.5rem;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-color: #e0e6ed;
        }

        .action-card .card-body {
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .action-card .card-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.2rem;
            font-size: 1.8rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-card h5 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0.5rem 0 0.25rem;
            color: #2c3e50;
        }

        .action-card .coming-soon {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 400;
            display: block;
            margin-top: 0.25rem;
        }

        /* Section Titles */
        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin: 2.5rem 0 1.5rem;
            position: relative;
            padding-bottom: 12px;
            font-size: 1.25rem;
        }

        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 4px;
            background: linear-gradient(45deg, #6777ef, #9c27b0);
            border-radius: 4px;
        }

        /* Charts */
        .chart-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #eef1f6;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }

        .chart-title i {
            margin-right: 10px;
            color: #6777ef;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .card-icon {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }

            .card-body {
                font-size: 1.5rem;
            }

            .action-card .card-body {
                padding: 1.5rem 1rem;
            }
        }
    </style> --}}
@endpush

@section('content')
    <div class="container">
        <section class="section">
            <div class="section-header">
                <h1>Admin Dashboard</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active">
                        <form action="{{ route('dashboard.switch') }}" method="POST" class="d-inline">
                            @csrf
                            <label class="form-check-label">
                                <span class="switch-label">
                                    Admin View
                                </span>
                            </label>
                            <div class="form-check form-switch d-inline-block ms-2">
                                <input class="form-check-input" style="width: 35px" type="checkbox" id="dashboardSwitch"
                                    onchange="this.form.submit()" {{ session('dashboard_mode') === 'employee' ? 'checked' : '' }}>
                                <label class="form-check-label" for="dashboardSwitch">
                                    <span class="switch-label">
                                        {{-- {{ session('dashboard_mode') === 'employee' ? 'Employee View' : 'Admin View' }}
                                        --}}
                                        Employee View
                                    </span>
                                </label>
                            </div>
                            <input type="hidden" name="mode"
                                value="{{ session('dashboard_mode') === 'employee' ? 'default' : 'employee' }}">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row emp-card">
                <!-- Total Employees -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 card-str-1">

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Active Employees</h4>

                                <div class="card-body">
                                    {{ $totalEmployees ?? 0 }}
                                </div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Departments -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mobile-space-01">
                    <div class="card card-statistic-1 card-str-2">

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Departments</h4>

                                <div class="card-body">
                                    {{ $departmentCount ?? 0 }}
                                </div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Today's Attendance -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mobile-space">
                    <div class="card card-statistic-1 card-str-3">

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Today's Attendance</h4>

                                <div class="card-body">
                                    {{ $todayAttendanceCount ?? 0 }}/{{ $totalEmployees ?? 0 }}
                                </div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests -->
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mobile-space">
                    <div class="card card-statistic-1 card-str-4">

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pending Requests</h4>

                                <div class="card-body">
                                    {{ $pendingRequests ?? 0 }}
                                </div>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-4">

                <div class="col-lg-5 px-1">
                    <div class="quick-actions card">
                        <h5 class="mt-3 pb-3 ps-3">Quick Actions</h5>


                        <!-- Employee Management -->
                        <div class="col-xl-12">
                            <a href="{{ route('company-admin.employees.index', ['companyId' => auth()->user()->company_id]) }}"
                                class="action-card">
                                <div class="card-body p-3">
                                    <div class="action-icon">
                                        <i class="fas fa-users-cog"></i>
                                        <div class="action-des ms-3">
                                            <h6>Manage Employees</h6>
                                            <span class="text-muted small">Add, edit, or remove employees</span>
                                        </div>
                                    </div>

                                </div>
                            </a>
                        </div>

                        <!-- Attendance -->
                        <div class="col-xl-12">
                            <a href="{{ route('attendance.dashboard') }}" class="action-card">
                                <div class="card-body p-3">
                                    <div class="action-icon">
                                        <i class="fas fa-calendar-check"></i>
                                        <div class="action-des ms-3">
                                            <h6>Attendance</h6>
                                            <span class="text-muted small">View and manage attendance</span>
                                        </div>
                                    </div>
                            </a>
                        </div>

                        <!-- Leave Management -->
                        <div class="col-xl-12">
                            <a href="{{ route('leave-requests.index') }}" class="action-card">
                                <div class="card-body p-3">
                                    <div class="action-icon">
                                        <i class="fas fa-calendar-minus"></i>
                                        <div class="action-des ms-3">
                                            <h6>Leave Management</h6>
                                            <span class="text-muted small">Approve or reject leave requests</span>
                                        </div>
                                    </div>
                            </a>
                        </div>

                        <!-- Reports -->
                        <div class="col-xl-12">
                            <a href="{{ route('announcements.index') }}" class="action-card">
                                <div class="card-body p-3">
                                    <div class="action-icon">
                                        <i class="fas fa-chart-bar"></i>
                                        <div class="action-des ms-3">
                                            <h6>Announcements</h6>

                                            <span class="text-muted small">Generate detailed reports</span>
                                        </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </div>
    </div>
    <div class="col-lg-7 px-1 mobile-space">
        <div class="card emp-calender">
            <div class="card-header">
                <h5>Calendar</h5>
            </div>
            <div class="card-body">
                <div id="employeeCalendar"></div>
            </div>
        </div>
    </div>
    </div>
<div class="row mt-4">
        <div class="col-lg-12 px-1 cash-dep">
            <div class="card">
                <h5>Announcement List</h5>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $filtered = $announcements->filter(function ($a) {
                                return $a->audience === 'both' || $a->audience === 'admins'; }); @endphp
                                @forelse ($filtered as $announcement)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $announcement->title }}</td>
                                        <td>{{ Str::limit($announcement->description, 50) }}</td>
                                        <td>{{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td>
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                if ($announcement->publish_date && $now->lt($announcement->publish_date)) {
                                                    $status = ['Upcoming', 'info'];
                                                } elseif ($announcement->expires_at && $now->gt($announcement->expires_at)) {
                                                    $status = ['Completed', 'success'];
                                                } else {
                                                    $status = ['Ongoing', 'warning'];
                                                }
                                            @endphp
                                            <span
                                                class="badge bg-{{ $status[1] }}{{ $status[0] == 'Ongoing' ? ' text-dark' : '' }}">{{ $status[0] }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('announcements.show', $announcement->id) }}"
                                                class="btn btn-sm btn-info">Show</a>
                                            @if($announcement->created_by === auth()->id())
                                                <a href="{{ route('announcements.edit', $announcement->id) }}"
                                                    class="btn btn-sm btn-primary ms-1">Edit</a>
                                                <form action="{{ route('announcements.destroy', $announcement->id) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger ms-1"
                                                        onclick="return confirm('Delete this announcement?')">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No announcements found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <div class="row mt-4">

        <div class="col-lg-8 px-1">
            <div class="card p-3">
                <h5 class="chart-title text-center">Employee Distribution by Department</h5>
                <canvas id="departmentChart"></canvas>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="col-lg-4 px-1 mobile-space">
            <div class="card p-3 admin-up-events">
                <h5 class="chart-title text-start btn-center">Upcoming Events / Holidays</h5>

                @forelse($academic_holidays as $holiday)
                    <div class="event-item">
                        <div class="event-date">
                            {{ \Carbon\Carbon::parse($holiday->from_date)->format('d') }}
                            <span>{{ \Carbon\Carbon::parse($holiday->from_date)->format('M') }}</span>
                        </div>
                        <div class="event-name">{{ $holiday->name ?? 'Holiday' }}</div>
                    </div>
                @empty
                    <p class="text-muted">No upcoming holidays</p>
                @endforelse
            </div>
        </div>
    </div>


    <div class="row mt-4">
        <div class="col-lg-6 px-1">
            <div class="card adnc-chart">
                <h5 class="text-center">Attendance Overview</h5>
                <div class="d-flex justify-content-center">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 px-1 mobile-space">
            {{-- <div class="card reg-req">
                <h5 class="text-center">Reimbursement Approvals</h5>
                <div class="d-flex justify-content-center">
                    <div class="card-body card p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                      <tbody>
                      @forelse ($reimbursements as $request)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $request->employee->name ?? 'N/A' }}</td>
                          <td>₹{{ $request->amount }}</td>
                          <td>
                            <button class="btn btn-sm btn-success">View</button>
                          </td>
                        </tr>

                      @empty

                      @endforelse
                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class=" px-1 mobile-space">

                    <div class="card today-not">
                        <h5 class="text-center">Reimbursement Approvals</h5>
                        <div class="Reimburs-table">
                            <table class="table table-bordered Reimbursements-table">
                                <thead>
                                    <tr>
                                        <th>S No.</th>
                                        <th>Date</th>
                                        <th>Employee</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="reimbursementTable">
                                    @forelse ($reimbursements as $reimbursement)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d M Y') }}
                                            </td>

                                            <td>{{ $reimbursement->employee->user->name ?? 'N/A'}}</td>

                                            <td>₹{{ number_format($reimbursement->amount, 2) }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $reimbursement->status === 'pending' ? 'warning' : ($reimbursement->status === 'reporter_approved' ? 'info' : ($reimbursement->status === 'admin_approved' ? 'success' : 'danger')) }}">
                                                    {{ ucfirst($reimbursement->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('reimbursements.show', $reimbursement->id) }}"
                                                class="btn btn-outline-info btn-sm me-1 action-btn"
                                                data-id="{{ $reimbursement->id }}" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="View Reimbursement" aria-label="View">
                                                <span class="btn-content">
                                                   <i class="fas fa-eye"></i>
                                                </span>
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                </a>

                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No reimbursements found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
        </div>
    </div>


    <div class="row mt-4">
           <div class="col-lg-6 px-1">
                        <div class="card birthday-text">
                            <h5>Upcoming events</h5>


                            <div class="event-wrapper">

                                <div class="background" id="eventBackground"></div>


                                @if ($upcoming_birthday)
                                    <div class="event-card">
                                        <h6>🎉 {{ ucwords($upcoming_birthday->name) }}’s Birthday Celebration</h6>
                                        <p>{{ \Carbon\Carbon::parse($upcoming_birthday->dob)->format('d F') }}</p>
                                    </div>
                                @else
                                    <div class="event-card">
                                        <h6>😅 Oops!</h6>
                                        <p>No upcoming birthdays 🎂</p>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>



        <div class="col-lg-6 px-1 mobile-space">
            <div class="card today-not">
                <h5 class="text-center">Today's Not Clock In</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Employee Name</th>
                                <th scope="col">Department</th>
                                <th scope="col">Expected Shift</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($absentees as $index => $employee)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                    <td>{{ $attendanceSettings ? \Carbon\Carbon::parse($attendanceSettings->office_start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($attendanceSettings->office_end_time)->format('h:i A') : 'N/A' }}</td>
                                </tr>

                            @empty

                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0 text-center">*These employees have not clocked in today</p>
            </div>
        </div>
    </div>



    </section>
    </div>


    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Attendance Pie Chart
      const ctxAttend = document.getElementById('attendanceChart').getContext('2d');
      new Chart(ctxAttend, {
        type: 'doughnut',
        data: {
          labels: ['Present', 'Absent', 'On Leave', 'Half Day', 'Late'],
          datasets: [{
            label: 'Employees',
            data: [
                  {{ $attendanceCounts['Present'] }},
                  {{ $attendanceCounts['Absent'] }},
                  {{ $attendanceCounts['On Leave'] }},
                  {{ $attendanceCounts['Half Day'] }},
                  {{ $attendanceCounts['Late'] }}
            ],
            backgroundColor: ['#4bc0c0', '#ff6384', '#ffcd56', '#9f2b68', '#9966ff'],
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });

            // Employee Bar Chart

      document.addEventListener('DOMContentLoaded', function () {
        const ctxDept = document.getElementById('departmentChart').getContext('2d');

        new Chart(ctxDept, {
          type: 'bar',
          data: {
            labels: @json($labels),
            datasets: [{
              label: 'Number of Employees',
              data: @json($data),
              backgroundColor: @json($colors),
              barPercentage: 0.4,
              categoryPercentage: 0.8,
            }]
          },
          options: {
            responsive: true,
            scales: {
              y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
              }
            }
          }
        });
      });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var calendarEl = document.getElementById('employeeCalendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },

                });
                calendar.render();
            });
        </script>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const bg = document.getElementById("eventBackground");

            // Balloons
            for (let i = 0; i < 8; i++) {
                let balloon = document.createElement("div");
                balloon.classList.add("balloon");
                balloon.style.left = Math.random() * 90 + "%";
                balloon.style.background = `hsl(${Math.random() * 360}, 70%, 60%)`;
                balloon.style.animationDuration = (5 + Math.random() * 5) + "s";
                bg.appendChild(balloon);
            }

            // Stars
            for (let i = 0; i < 25; i++) {
                let star = document.createElement("div");
                star.classList.add("star");
                star.style.left = Math.random() * 100 + "%";
                star.style.top = Math.random() * 100 + "%";
                star.style.animationDuration = (1 + Math.random() * 2) + "s";
                bg.appendChild(star);
            }

            // Confetti
            for (let i = 0; i < 15; i++) {
                let conf = document.createElement("div");
                conf.classList.add("confetti");
                conf.style.left = Math.random() * 100 + "%";
                conf.style.background = `hsl(${Math.random() * 360}, 80%, 60%)`;
                conf.style.animationDuration = (3 + Math.random() * 3) + "s";
                bg.appendChild(conf);
            }
        });
    </script>

    @endpush

@endsection
