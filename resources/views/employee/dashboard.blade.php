@extends('layouts.app')
@section('title', 'Employee Dashboard')

@section('content')
  <div class="main-content-01 container">
    <section class="section">
      <div class="section-header">
        <h1>Employee Dashboard</h1>
        <div class="section-header-breadcrumb">
          <div class="breadcrumb-item active">Welcome, {{ auth()->user()->name }}!</div>
        </div>
      </div>

      <div class="section-body">
        <!-- Quick Access Cards -->
        <div class="row">
          <div class="col-lg-3 px-1">
            <div class="employee-check-in">


              <div class="col-lg-12">
                <a href="{{ route('attendance.check-in') }}" class="card card-link">
                  <div class="card-body text-center d-flex align-items-center">

                    <i class="fas fa-clock fa-3x"></i>

                    <h6 class="card-title mb-0 ms-2">Check In/Out</h6>
                  </div>
                </a>
              </div>

              <div class="col-lg-12">
                <a href="{{ route('attendance.my-attendance') }}" class="card card-link">
                  <div class="card-body text-center d-flex align-items-center">

                    <i class="fas fa-calendar-check fa-3x"></i>

                    <h6 class="card-title mb-0 ms-2">My Attendance</h6>
                  </div>
                </a>
              </div>

              <div class="col-lg-12">
                <a href="{{ route('employee.leave-requests.create') }}" class="card card-link">
                  <div class="card-body text-center d-flex align-items-center">

                    <i class="fas fa-calendar-plus fa-3x"></i>

                    <h6 class="card-title mb-0 ms-2">Apply Leave</h6>
                  </div>
                </a>
              </div>

              <div class="col-lg-12 salary-details">
                <a href="{{ route('employee.salary.details') }}" class="card card-link">
                  <div class="card-body text-center d-flex align-items-center">

                    <i class="fas fa-money-bill-wave fa-3x"></i>

                    <h6 class="card-title mb-0 ms-2">Salary Details</h6>
                  </div>
                </a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 px-1 mobile-space">
            <div class="card income-breakdown">
              <div class="chart-container">
                <h5>Income Breakdown</h5>
                <p>8% High than last month</p>
                <canvas id="incomeChart"></canvas>
              </div>

            </div>
          </div>
          <div class="col-lg-6 px-1 mobile-space">
            <div class="card emp-calender">
              <div class="card-header">
                <h5>Employee Calendar</h5>
              </div>
              <div class="card-body">
                <div id="employeeCalendar"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Status Cards -->
        <div class="row mt-4">
          <div class="col-lg-6 px-1">
            <div class="today-check-in">
              <div class="card emp-dash-h4">
                <h5>Today's Status</h5>
              </div>
              <div class="card-body emp-dashboard">
                <div class="row align-items-center today-status">
                  <div class="col-4 text-center">
                    <i class="fas fa-user-clock fa-3x"></i>
                  </div>
                  <div class="col-8">
                    <div class="mb-2">
                      <strong>Check In Time:</strong>
                      @if(isset($todayAttendance) && $todayAttendance->check_in)
                        <span class="ml-2">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</span>
                      @else
                        <span class="ml-2 text-muted">Not checked in</span>
                      @endif
                    </div>
                    <div class="mb-2">
                      <strong>Check Out Time:</strong>
                      @if(isset($todayAttendance) && $todayAttendance->check_out)
                        <span class="ml-2">{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}</span>
                      @else
                        <span class="ml-2 text-muted">Not checked out</span>
                      @endif
                    </div>
                    <div>
                      <strong>Status:</strong>
                      @if(isset($todayAttendance))
                        @if($todayAttendance->status === 'Present')
                          <span class="badge badge-success">Present</span>
                        @elseif($todayAttendance->status === 'Late')
                          <span class="badge badge-warning">Late</span>
                        @elseif($todayAttendance->status === 'Absent')
                          <span class="badge badge-danger">Absent</span>
                        @elseif($todayAttendance->status === 'On Leave')
                          <span class="badge badge-info">On Leave</span>
                        @else
                          <span class="badge badge-secondary">{{ $todayAttendance->status }}</span>
                        @endif

                        @if($todayAttendance->check_in && $todayAttendance->check_out)
                          @php
                            $checkIn = \Carbon\Carbon::parse($todayAttendance->check_in);
                            $checkOut = \Carbon\Carbon::parse($todayAttendance->check_out);
                            $hours = $checkOut->diffInHours($checkIn);
                            $minutes = $checkOut->diffInMinutes($checkIn) % 60;
                          @endphp
                          <span class="ml-2 text-muted">({{ sprintf('%d:%02d', $hours, $minutes) }} hrs)</span>
                        @endif
                      @else
                        <span class="badge badge-warning">Not Checked In</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>





              <div class="card emp-dash-h4">
                <h5>Leave Balance</h5>
              </div>
              <div class="card-body emp-dashboard">
                <div class="row align-items-center">
                  <div class="col-4 text-center">
                    <i class="fas fa-calendar-alt fa-3x"></i>
                  </div>
                  <div class="col-8">
                    <div class="mb-2">
                      <strong>Available Leaves:</strong>
                      <span class="ml-2">{{ $leaveBalance ?? 0 }} Days</span>
                    </div>
                    <a href="{{ route('employee.leave-requests.index') }}" class="btn btn-sm btn-warning">
                      View Leave History
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 px-1 monthly-leave mobile-space">
            <div class="card">
              <h5>Monthly Leave Summary</h5>
              
              <canvas id="leaveChart"></canvas>
              
            </div>
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-6 px-1">
            <div class="card birthday-text">
              <h5>Upcoming events</h5>


              <div class="event-wrapper">

                <div class="background" id="eventBackground"></div>


                @if($upcoming_birthday)
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
            <div class="card holiday-table">
            
                <h5 class="text-center">Upcoming Holidays</h5>
              
              <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Date</th>
                      <th>Day</th>
                      <th>Holiday</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($academic_holidays as $holiday)
                      <tr>
                        <td>{{ \Carbon\Carbon::parse($holiday->from_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($holiday->from_date)->format('l') }}</td>
                        <td>{{ $holiday->name }}</td>
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
        <div class="row mt-4">
          <div class="col-lg-8 px-1 cash-dep">
            <div class="card">
              <h5>Attendance summary</h5>
              <canvas id="attendanceChart"></canvas>

            </div>
          </div>
          <div class="col-lg-4 px-1 cash-dep mobile-space">
            <div class="card leave">
              <h5>Leave Taken vs Remaining</h5>
              <canvas id="leaveBalanceChart"></canvas>

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
              @php $filtered = $announcements->filter(function($a) { return $a->audience === 'employees' || $a->audience === 'both'; }); @endphp
              @forelse ($filtered as $announcement)
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
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
  <script>
    const leaveBalanceCtx = document.getElementById('leaveBalanceChart').getContext('2d');
    const totalLeaves = {{ $total_leaves ?? 0 }};
    const leavesTaken = {{ $leaves_taken ?? 0 }};
    const remainingLeaves = {{ $leave_balance ?? 0 }};

    new Chart(leaveBalanceCtx, {
      type: 'doughnut',
      data: {
        labels: ['Leave Taken', 'Remaining'],
        datasets: [{
          data: [leavesTaken, remainingLeaves],
          backgroundColor: ['#ff9705', '#ff970595'],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: function (context) {
                return context.label + ': ' + context.raw + ' days';
              }
            }
          }
        }
      }
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
    document.addEventListener("DOMContentLoaded", function () {
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
  <script>
    // Income Chart (Pie)
    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    new Chart(incomeCtx, {
      type: 'pie',
      data: {
        labels: ['HR', 'Development', 'Sales', 'Marketing'],
        datasets: [{
          data: [25, 35, 20, 20],
          backgroundColor: ['#003366', '#ff9705', '#28a745', '#f1c40f'],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
      }
    });

    // Leave Chart (Bar)
    const leaveCtx = document.getElementById('leaveChart').getContext('2d');
    // Ensure leave data is always integer
    const monthlyLeavesTaken = @json($monthlyLeavesTaken ?? array_fill(0, 12, 0)).map(x => Math.round(x));
    new Chart(leaveCtx, {
      type: 'bar',
      data: {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        datasets: [{
          label: 'Leaves Taken',
          data: monthlyLeavesTaken,
          backgroundColor: [
            '#003f5c', '#ff9705', '#88d498', '#845ec2', '#00c9a7',
            '#c34a36', '#2cb6e2', '#b9314f', '#8bc34a', '#6a4c93',
            '#29b6f6', '#ffb300'
          ],
          borderRadius: 8
        }]
      },
       options: {
      responsive: true,
      maintainAspectRatio: false, // ✅ allows chart to stretch on smaller screens
      plugins: { 
        legend: { display: false } 
      },
      scales: {
        x: {
          ticks: {
            maxRotation: 45, // ✅ rotate labels on mobile for better fit
            minRotation: 30
          }
        },
        y: {
          beginAtZero: true,
          ticks: { precision: 0 } // ✅ integer ticks
        }
      }
    }
  });


    // Attendance Summary (Line Chart with 4 metrics)
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');

    const chartData = @json($monthlyData);
    const currentYear = @json($currentYear);

    new Chart(attendanceCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
          {
            label: 'Present',
            data: Object.values(chartData.Present),
            borderColor: '#22c55e',
            backgroundColor: 'rgba(34,197,94,0.2)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#22c55e',
            pointRadius: 4
          },
          {
            label: 'Absent',
            data: Object.values(chartData.Absent),
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239,68,68,0.2)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ef4444',
            pointRadius: 4
          },
          {
            label: 'Late',
            data: Object.values(chartData.Late),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.2)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 4
          },
          {
            label: 'On Leave',
            data: Object.values(chartData["On Leave"]),
            borderColor: '#eab308',
            backgroundColor: 'rgba(234,179,8,0.2)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#eab308',
            pointRadius: 4
          },
          {
            label: 'Half Day',
            data: Object.values(chartData["Half Day"]),
            borderColor: '#DE3163',
            backgroundColor: 'rgba(222, 49, 99,0.3)',
            borderWidth: 2,
            fill: true,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' },
          title: { display: true, text: 'Monthly Attendance Trend (' + currentYear + ')' }
        },
        scales: {
          x: { title: { display: true, text: 'Month' } },
          y: {
            title: { display: true, text: 'Number of Days' },
            beginAtZero: true,
            suggestedMax: 30
          }
        }
      }
    });
  </script>



@endpush