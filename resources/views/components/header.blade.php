@auth
<!-- Add in your Blade layout or before </body> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Left Side -->
        
        <div class="d-flex align-items-center">
            <!-- Sidebar Toggle Button -->
      
            <a href="#"  data-toggle="sidebar" class="nav-link nav-link-lg me-3 me-lg-4">
                <i class="fas fa-bars"></i>
            </a>
            {{-- <a href="#"  class="nav-link nav-link-lg me-3 me-lg-4" ><i class="fas fa-bars"></i></a> --}}


            <!-- Brand for Mobile -->
           <div class="mobile-logo logo"> 
            <a href="#"><img alt="image" src="{{ asset('images\logo (1).png') }}" width="100px"></a> 
        </div>
        </div>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-ellipsis-v"></i>
        </button>

        <!-- Collapsible Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Quick Actions - Shows on larger screens inline, on mobile in collapse -->
             <div class="sidebar-brand logo">
                <a href="#"><img alt="image" src="{{ asset('images\logo (1).png') }}" width="150px"></a>
            </div>
            <div class="quick-actions quick-actions-header my-3 my-lg-0 mx-lg-4">
                <div class="d-grid d-lg-flex gap-2">
                    @if(!Auth::user()->hasRole(['superadmin']))
                    <a href="{{ route('attendance.check-in') }}" class="btn btn-light">
                        <i class="fas fa-clock me-2"></i> Quick Attendance
                    </a>
                    @endif
                    @if(Auth::user()->hasRole(['user', 'employee']))
                    <a href="{{ route('reimbursements.create') }}" class="btn btn-light">
                        <i class="fas fa-receipt me-2"></i> New Reimbursement
                    </a>
                    @endif
                </div>
            </div>
            </div>
        </div>

        <!-- Right Side -->
        <ul class="navbar-nav ms-auto d-flex align-items-center">

            <!-- User Menu -->
            <li class="nav-item dropdown ms-2">
                <a href="#" class="nav-link dropdown-toggle nav-link-lg nav-link-user d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <img alt="image" src="{{ asset('img/avatar/avatar-1.png') }}" class="rounded-circle me-2" width="32">
                    <div class="d-none d-lg-inline-block">
                        <span class="fw-medium">{{ auth()->user()->name }}</span>
                        <small class="d-block text-muted">{{ auth()->user()->roles && auth()->user()->roles->first() ? ucfirst(auth()->user()->roles->first()->name) : 'User' }}</small>
                    </div>
                    <div class="d-lg-none">
                        <span class="fw-medium">{{ Str::words(auth()->user()->name, 1, '') }}</span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <div class="dropdown-header border-bottom p-3">
                            <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                            <small class="text-muted">{{ auth()->user()->email }}</small>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user me-2"></i> Edit Profile
                        </a>
                    </li>
                    @if(Auth::user()->hasRole(['user', 'employee']))
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('employee.profile') }}">
                            <i class="fas fa-id-card me-2"></i> My Employee Profile
                        </a>
                    </li>
                    @endif
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a href="{{ route('logout') }}" class="dropdown-item text-danger py-2"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>

</nav>
@endauth
