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
                <div class="breadcrumb-item"><a href="">Companies Users</a></div>
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
                        
                        <div class="card-body">
                            {{ $todayAttendanceCount }}/{{ $totalEmployees }}
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
                            <h4>Today's Attendance</h4>
                        
                        <div class="card-body">
                            {{ $todayAttendanceCount }}/{{ $totalEmployees }}
                        </div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
</div>

        <!-- Quick Actions Section -->
        
           
            <div class="row mt-4">
 <div class="col-6 px-1">
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
      <i class="bi bi-person-plus-fill fs-1"></i></div>
      <div class="joinee">
      <h6>New Joinees</h6>
      <h2>12</h2>
      <small class="text-muted">Joined this month</small>
</div>
    </div>

    <!-- Resigned Employees -->
    <div class="joinee-resign">
        <div class="icon">
      <i class="bi bi-person-dash-fill fs-1"></i></div>
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
   <div class="card reg-req">
        <h5 class="text-center">Today's Clock In</h5>
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
      <tr>
        <td>1</td>
        <td>Neha</td>
        <td>
          <button class="btn btn-sm btn-success me-2">check in</button>
          <button class="btn btn-sm btn-danger me-2">check out</button>
          <button class="btn btn-sm btn-warning">Late</button>
        </td>
      </tr>
       <tr>
        <td>2</td>
        <td>Nidhi</td>
        <td>
           <button class="btn btn-sm btn-success me-2">check in</button>
          <button class="btn btn-sm btn-danger me-2">check out</button>
          <button class="btn btn-sm btn-warning">Late</button>

        </td>
      </tr>
       <tr>
        <td>3</td>
        <td>Rahul</td>
        <td>
           <button class="btn btn-sm btn-success me-2">check in</button>
          <button class="btn btn-sm btn-danger me-2">check out</button>
          <button class="btn btn-sm btn-warning">Late</button>

        </td>
      </tr>
       <tr>
        <td>4</td>
        <td>Rohan</td>
        <td>
           <button class="btn btn-sm btn-success me-2">check in</button>
          <button class="btn btn-sm btn-danger me-2">check out</button>
          <button class="btn btn-sm btn-warning">Late</button>
         
        </td>
      </tr>
    </tbody>
  </table>
</div>
  </div>
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
            <tr>
              <th scope="row">1</th>
              <td>Amit Sharma</td>
              <td>Development</td>
              <td>09:00 AM - 06:00 PM</td>
            </tr>
            <tr>
              <th scope="row">2</th>
              <td>Neha Singh</td>
              <td>Sales</td>
              <td>10:00 AM - 07:00 PM</td>
            </tr>
            <tr>
              <th scope="row">3</th>
              <td>Raj Patel</td>
              <td>Support</td>
              <td>08:00 AM - 05:00 PM</td>
            </tr>
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
      <tr>
        <td>1</td>
        <td>Neha</td>
        <td>
          <button class="btn btn-sm btn-success">View</button>
         
        </td>
      </tr>
       <tr>
        <td>2</td>
        <td>Nidhi</td>
        <td>
          <button class="btn btn-sm btn-success">View</button>
         
        </td>
      </tr>
       <tr>
        <td>3</td>
        <td>Rahul</td>
        <td>
          <button class="btn btn-sm btn-success">View</button>
         
        </td>
      </tr>
       <tr>
        <td>4</td>
        <td>Rohan</td>
        <td>
          <button class="btn btn-sm btn-success">View</button>
         
        </td>
      </tr>
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
      <tr>
        <td>26 Jan 2025</td>
        <td>Sunday</td>
        <td>Republic Day</td>
      </tr>
      <tr>
        <td>14 Apr 2025</td>
        <td>Monday</td>
        <td>Ambedkar Jayanti</td>
      </tr>
      <tr>
        <td>1 May 2025</td>
        <td>Thursday</td>
        <td>Labour Day</td>
      </tr>
      <tr>
        <td>1 May 2025</td>
        <td>Thursday</td>
        <td>Labour Day</td>
      </tr>
      <tr>
        <td>1 May 2025</td>
        <td>Thursday</td>
        <td>Labour Day</td>
      </tr>
      
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
              <tr>
                <td>1</td>
                <td>Holiday Notice</td>
                <td>Office will remain closed on 15th August for Independence Day.</td>
                <td>2025-08-10</td>
                <td><span class="badge bg-info">Upcoming</span></td>
                <td>
                  <button class="btn btn-sm btn-primary">Edit</button>
                  <button class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>New HR Policy</td>
                <td>Updated leave policy effective from September 1st.</td>
                <td>2025-08-05</td>
                <td><span class="badge bg-warning text-dark">Ongoing</span></td>
                <td>
                  <button class="btn btn-sm btn-primary">Edit</button>
                  <button class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
              <tr>
                <td>3</td>
                <td>Team Outing</td>
                <td>Annual team outing scheduled for September 15th.</td>
                <td>2025-07-30</td>
                <td><span class="badge bg-success">Completed</span></td>
                <td>
                  <button class="btn btn-sm btn-primary">Edit</button>
                  <button class="btn btn-sm btn-danger">Delete</button>
                </td>
              </tr>
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
</script>
<script>
  const ctxAttend = document.getElementById('attendanceChart').getContext('2d');
  new Chart(ctxAttend, {
    type: 'doughnut',
    data: {
      labels: ['Present', 'Absent', 'On Leave'],
      datasets: [{
        label: 'Employees',
        data: [115, 5, 8],
        backgroundColor: ['#4bc0c0', '#ff6384', '#ffcd56']
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
  const ctxDept = document.getElementById('departmentChart').getContext('2d');
  new Chart(ctxDept, {
    type: 'bar',
    data: {
      labels: ['Marketing', 'Sales', 'IT', 'HR', 'Finance'],
      datasets: [{
        label: 'Number of Employees',
        data: [20, 30, 40, 15, 23], 
        backgroundColor: [
          '#ffcd56', '#ff6384', '#4bc0c0', '#36a2eb', '#9966ff'
        ]
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 5
          }
        }
      }
    }
  });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
