@auth
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="">RokcetHR</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="">RokcetHR</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            <li class="{{ Request::is('home') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('home') }}"><i class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>

            {{-- SuperAdmin Routes --}}
            @if (Auth::user()->role == 'superadmin')
           
            <li class="menu-header">Companies</li>
            <li class="{{ Request::is('superadmin/companies') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('superadmin.companies.index') }}"><i class="fas fa-building"></i> <span>Manage Companies</span></a>
            </li>
            @endif

            <!-- {{-- Company Admin Routes --}}
            @if (Auth::user()->role == 'admin')
            <li class="menu-header">Employees</li>
            <li class="{{ Request::is('company/companies/*/employees') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.employees.index', ['companyId' => Auth::user()->company_id]) }}"><i class="fas fa-users"></i> <span>Manage Employees</span></a>
            </li>
            <li class="{{ Request::is('company/companies/*/employees/create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.employees.create', ['companyId' => Auth::user()->company_id]) }}"><i class="fas fa-user-plus"></i> <span>Add Employee</span></a>
            </li>
            @endif -->

            {{-- Company Admin Routes --}}
            @if (Auth::user()->role == 'admin')
            <li class="menu-header">Employees</li>
            <li class="{{ Request::is('company/companies/*/employees') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.employees.index' }}"><i class="fas fa-users"></i> <span>Manage Employees</span></a>
            </li>
            <li class="{{ Request::is('company/companies/*/employees/create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.employees.create' }}"><i class="fas fa-user-plus"></i> <span>Add Employee</span></a>
            </li>
            @endif

            {{-- Employee Routes --}}
            @if (Auth::user()->role == 'user')
            <li class="menu-header">Profile</li>
            <li class="{{ Request::is('employee/profile') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('employee.profile') }}"><i class="far fa-user"></i> <span>My Profile</span></a>
            </li>
            @endif

            <!-- Profile, Change Password and other sections -->
            <li class="menu-header">Profile</li>
            <li class="{{ Request::is('profile/edit') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('profile/edit') }}"><i class="far fa-user"></i> <span>Profile</span></a>
            </li>
            <li class="{{ Request::is('profile/change-password') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('profile/change-password') }}"><i class="fas fa-key"></i> <span>Change Password</span></a>
            </li>

            <!-- Example Links (for your reference) -->
            <li class="menu-header">Starter</li>
            <li class="{{ Request::is('blank-page') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('blank-page') }}"><i class="far fa-square"></i> <span>Blank Page</span></a>
            </li>
            <li class="menu-header">Examples</li>
            <li class="{{ Request::is('table-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('table-example') }}"><i class="fas fa-table"></i> <span>Table Example</span></a>
            </li>
            <li class="{{ Request::is('clock-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('clock-example') }}"><i class="fas fa-clock"></i> <span>Clock Example</span></a>
            </li>
            <li class="{{ Request::is('chart-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('chart-example') }}"><i class="fas fa-chart-bar"></i> <span>Chart Example</span></a>
            </li>
            <li class="{{ Request::is('form-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('form-example') }}"><i class="fas fa-file-alt"></i> <span>Form Example</span></a>
            </li>
            <li class="{{ Request::is('map-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('map-example') }}"><i class="fas fa-map"></i> <span>Map Example</span></a>
            </li>
            <li class="{{ Request::is('calendar-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('calendar-example') }}"><i class="fas fa-calendar"></i> <span>Calendar Example</span></a>
            </li>
            <li class="{{ Request::is('gallery-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('gallery-example') }}"><i class="fas fa-images"></i> <span>Gallery Example</span></a>
            </li>
            <li class="{{ Request::is('todo-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('todo-example') }}"><i class="fas fa-list"></i> <span>Todo Example</span></a>
            </li>
            <li class="{{ Request::is('contact-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('contact-example') }}"><i class="fas fa-envelope"></i> <span>Contact Example</span></a>
            </li>
            <li class="{{ Request::is('faq-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('faq-example') }}"><i class="fas fa-question-circle"></i> <span>FAQ Example</span></a>
            </li>
            <li class="{{ Request::is('news-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('news-example') }}"><i class="fas fa-newspaper"></i> <span>News Example</span></a>
            </li>
            <li class="{{ Request::is('about-example') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('about-example') }}"><i class="fas fa-info-circle"></i> <span>About Example</span></a>
            </li>
        </ul>
    </aside>
</div>
@endauth
