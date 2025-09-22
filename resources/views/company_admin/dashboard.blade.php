@extends('layouts.app')
@section('title', 'Company Admin Dashboard')

@push('styles')
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
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      transition: all 0.2s ease;
      border: 1px solid #eef1f6;
      height: 100%;
      text-decoration: none;
      color: #343a40;
    }

    .action-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
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
  </style> --}}
@endpush

@section('content')
  <div class="main-content-01 container">
    <section class="section">
      <div class="section-header">
        <h1>Dashboard Overview</h1>
        <div class="section-header-breadcrumb">
          @if(auth()->user()->role === 'admin')
          <div class="breadcrumb-item active">
            <a href="http://127.0.0.1:8000/home">Dashboard</a>
            <form action="{{ route('dashboard.switch') }}" method="POST" class="d-inline ms-3">
              @csrf
              <div class="form-check form-switch d-inline-block">
                <input class="form-check-input" type="checkbox" id="dashboardSwitch" 
                       onchange="this.form.submit()" 
                       {{ session('dashboard_mode') === 'employee' ? 'checked' : '' }}>
                <label class="form-check-label" for="dashboardSwitch">
                  <span class="switch-label">
                    {{ session('dashboard_mode') === 'employee' ? 'Employee View' : 'Admin View' }}
                  </span>
                </label>
              </div>
              <input type="hidden" name="mode" value="{{ session('dashboard_mode') === 'employee' ? 'default' : 'employee' }}">
            </form>
          </div>
          @endif
          <div class="breadcrumb-item">Companies Users</div>
        </div>
       
      </div>

      <div class="row emp-card">

        <!-- Employees Card -->
        <div class="col-lg-3 px-1">
          <div class="card card-statistic-1 card-hover card-str-1">

            <div class="card-wrap">
              <div class="card-header">
                <h4>Total Employees</h4>

                <div class="card-body">
                  {{ array_sum($companyRoleData->toArray()) }}
                </div>
              </div>

              <div class="card-icon">
                <i class="fas fa-users"></i>

              </div>
            </div>
          </div>
        </div>

        <!-- Departments Card -->
        <div class="col-lg-3 px-1">
          <div class="card card-statistic-1 card-hover card-str-2">

            <div class="card-wrap">
              <div class="card-header">
                <h4>Departments</h4>

                <div class="card-body">
                  {{ $departmentCount }}
                </div>
              </div>
              <div class="card-icon">
                <i class="fas fa-building"></i>
              </div>
            </div>
          </div>
        </div>


        <!-- Today's Attendance -->
        <div class="col-lg-3 px-1">
          <div class="card card-statistic-1 card-hover card-str-3">

            <div class="card-wrap">
              <div class="card-header">
                <h4>Today's Attendance</h4>

                <div id="presentCount" class="card-body">
                  {{ $presentees_count }}
                </div>
              </div>
              <div class="card-icon">
                <i class="fas fa-calendar-check"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 px-1">
          <div class="card card-statistic-1 card-hover card-str-4">

            <div class="card-wrap">
              <div class="card-header">
                <h4>Today's Absentees</h4>

                <div class="card-body">
                  {{ $absentees_count }}
                </div>
              </div>
              <div class="card-icon">
                <i class="fas fa-calendar-xmark"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions Section -->


      <div class="row mt-4">
        <div class="col-lg-6 col-sm-12 px-1">
          <div class="card emp-department p-4">
            <h5 class="mb-3 text-center">Employee Distribution by Department</h5>
            <canvas id="departmentChart"></canvas>
          </div>
        </div>
        <div class="col-lg-6 px-1">
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
        <div class="col-lg-4 px-1">
          <div class="quick-actions card">
            <h5 class="pt-3 pb-3 ps-3">Quick Actions</h5>
            <!-- Attendance Card -->
            <div class="col-xl-12">
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
            <div class="col-xl-12">
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
            <div class="col-xl-12">
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
            <div class="col-xl-12">
              <a href="{{ route('company.employees.index', ['companyId' => auth()->user()->company_id]) }}"
                class="action-card h-100">
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
        <div class="col-4 px-1">
          <div class="card p-4 attendance-height">
            <h5 class="mb-3 attendance-card">Attendance Overview</h5>
            <canvas id="attendanceChart"></canvas>
          </div>
        </div>


        <div class="col-4 px-1">
          <div class="card card-glass new-old-emp">
            <h5 class="mb-4">Employee Movement</h5>
            <!-- New Joinees -->
            <div class="mb-4 joinee-resign">
              <div class="icon">
                <i class="bi bi-person-plus-fill fs-1"></i>
              </div>
              <div class="joinee">
                <h6>New Joinees</h6>
                <h2>
                  {{ $newJoineesCount }}
                </h2>
                <small class="text-muted">Employees joined</small>
              </div>
            </div>

            <!-- Resigned Employees -->
            <div class="joinee-resign">
              <div class="icon">
                <i class="bi bi-person-dash-fill fs-1"></i>
              </div>
              <div class="joinee">
                <h6>Resigned This Month</h6>
                <h2>3</h2>
                <small class="text-muted">Employees left</small>
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
        <div class="col-lg-6 px-1">
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
                      <th scope="row">{{ $employee->id }}</th>
                      <td>{{ $employee->name }}</td>
                      <td>{{ $employee->department->name ?? 'N/A' }}</td>
                      <td>10:00 AM - 06:30 PM</td>
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


      <div class="row mt-4">

        <div class="col-lg-7 px-1">
          <div class="card today-not">
            <h5 class="text-center">Meeting Schedule</h5>
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Date</th>
                    <th scope="col">Time</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row">1</th>
                    <td>New Technology</td>
                    <td>Apr 12, 2025</td>
                    <td>3:20 PM</td>
                  </tr>
                  <tr>
                    <th scope="row">2</th>
                    <td>Team Meeting</td>
                    <td>May 26, 2025</td>
                    <td>5:00 PM</td>
                  </tr>
                  <tr>
                    <th scope="row">3</th>
                    <td>Event Related</td>
                    <td>Jun 10, 2025</td>
                    <td>2:25 PM</td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>
        <div class="col-lg-5 px-1">
          <div class="card reg-req">
            <h5 class="text-center">Regularization Requests</h5>
            <div class="d-flex justify-content-center">
              <div class="card-body card p-0">
                <div class="table-responsive">
                  <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($pending_regularization_requests as $request)
                        <tr>
                          <td>{{ $request->id }}</td>
                          <td>{{ $request->employee->name ?? 'N/A' }}</td>
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
          </div>
        </div>

      </div>

      <div class="row mt-4">
        <div class="col-lg-5 px-1">
          <div class="card holiday-table">
            <div class="card-header">
              <h5 class>Upcoming Holidays</h5>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Holiday</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($academic_holidays as $holidays)
                    <tr>
                      <td>{{ Carbon\Carbon::parse($holidays->from_date)->format('d M Y') }}</td>
                      <td>{{ Carbon\Carbon::parse($holidays->from_date)->format('l') }}</td>
                      <td>{{ $holidays->name }}</td>
                    </tr>
                  @empty

                  @endforelse

                </tbody>
              </table>
            </div>

  </div>
    </div>
    <div class="col-lg-7 px-1">
        <div class="card">
<div class="card-body">
<h5 class="text-center fw-bold">Department-wise Employee Count</h5>
<div class="chart-wrap">
<canvas id="payrollChart"></canvas>
</div>
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
              @forelse ($announcements as $announcement)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $announcement->title }}</td>
                  <td>{{ Str::limit($announcement->description, 50) }}</td>
                  <td>{{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->format('Y-m-d') : '-' }}</td>
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
                    <span class="badge bg-{{ $status[1] }}{{ $status[0] == 'Ongoing' ? ' text-dark' : '' }}">{{ $status[0] }}</span>
                  </td>
                  <td>
                    <a href="{{ route('company-admin.announcements.show', $announcement->id) }}" class="btn btn-sm btn-info">Show</a>
                    <a href="{{ route('company-admin.announcements.edit', $announcement->id) }}" class="btn btn-sm btn-primary ms-1">Edit</a>
                    <form action="{{ route('company-admin.announcements.destroy', $announcement->id) }}" method="POST" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-danger ms-1" onclick="return confirm('Delete this announcement?')">Delete</button>
                    </form>
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
    </section>
  </div>

  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script>
      const ctx = document.getElementById('payrollChart');


      const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
      gradient.addColorStop(0, 'rgba(255, 99, 132, 0.4)');
      gradient.addColorStop(1, 'rgba(255, 99, 132, 0)');


      new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          datasets: [{
            label: 'Payroll Expense (₹ in Lakhs)',
            data: [12, 15, 13, 14, 16, 18, 17, 19, 20, 18, 17, 21],
            fill: true,
            backgroundColor: gradient,
            borderColor: '#ff5c5c',
            borderWidth: 2,
            tension: 0.3,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#ff5c5c',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              labels: {
                boxWidth: 20,
                usePointStyle: false,
                font: {
                  size: 13,
                  weight: 'bold'
                }
              },
              position: 'top',
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: (val) => `₹${val}L`
              },
              grid: { drawBorder: false }
            },
            x: {
              grid: { display: false }
            }
          }
        }
      });

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
    </script>

    <script>
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
            left: 'prev,next',
            center: 'title',
            right: ''
          },

        });
        calendar.render();
      });
    </script>
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