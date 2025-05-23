@auth
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
        <a href="">PayNinja</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
        <a href="">PayNinja</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            <li class="{{ Request::is('home') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('home') }}"><i class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>
            @if (Auth::user()->hasRole('superadmin'))
            <li class="menu-header">Companies</li>
            <li class="{{ Request::is('hakakses') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('hakakses') }}"><i class="fas fa-user-shield"></i> <span>All Users</span></a>
            </li>
            <li class="{{ Request::is('superadmin/companies') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('superadmin.companies.index') }}"><i class="fas fa-building"></i> <span>Manage Companies</span></a>
            </li>
            @endif


            <!-- {{-- or 'employee' --}} -->
            @if (Auth::user()->hasRole(['user', 'employee'])) 
    <li class="menu-header">Profile</li>
    <li class="{{ Request::is('employee/profile') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('employee.profile') }}"><i class="far fa-user"></i> <span>My Profile</span></a>
    </li>
    <li class="{{ Request::is('employee/colleagues') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('employee.colleagues') }}"><i class="fas fa-users"></i> <span>My Colleagues</span></a>
    </li>
    
    <li class="menu-header">Leave Management</li>
    <li class="{{ Request::is('leave-management/leave-requests') && !Request::is('leave-management/leave-requests/create') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('leave-management.leave-requests.index') }}"><i class="fas fa-clipboard-list"></i> <span>My Leave Requests</span></a>
    </li>
    <li class="{{ Request::is('leave-management/leave-requests/create') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('leave-management.leave-requests.create') }}"><i class="fas fa-calendar-plus"></i> <span>Apply for Leave</span></a>
    </li>
    <li class="{{ Request::is('leave-management/leave-requests/calendar') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('leave-management.leave-requests.calendar') }}"><i class="fas fa-calendar-alt"></i> <span>Leave Calendar</span></a>
    </li>
    <li class="menu-header">Reimbursements</li>
            <li class="{{ Request::is('reimbursements') && !Request::is('reimbursements/create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reimbursements.index') }}"><i class="fas fa-receipt"></i> <span>My Reimbursements</span></a>
            </li>
            <li class="{{ Request::is('reimbursements/create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reimbursements.create') }}"><i class="fas fa-plus-circle"></i> <span>Request Reimbursement</span></a>
            </li>         
           
@endif

            {{-- Company Admin Routes --}}
            @if (Auth::user()->hasRole('company_admin') || Auth::user()->hasRole('admin'))
            <li class="menu-header">Company Management</li>

         

            <li class="{{ Request::is('company-admin/employees*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company-admin.employees.index') }}"><i class="fas fa-users"></i> <span>Employee Management</span></a>
            </li>

            <li class="{{ Request::is('company-admin/module-access*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company-admin.module-access.index') }}"><i class="fas fa-key"></i> <span>Module Access</span></a>
            </li>
            <li class="{{ Request::is('company/companies/*/employees/create') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('company.employees.create', ['companyId' => Auth::user()->company_id]) }}"><i class="fas fa-user-plus"></i> <span>Add Employee</span></a>
            <li class="{{ Request::is('company-admin/settings*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company-admin.settings.index') }}"><i class="fas fa-cog"></i> <span>Company Settings</span></a>
            </li>
            <li class="{{ Request::is('company/designations*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company.designations.index') }}"><i class="fas fa-id-badge"></i> <span>Manage Designations</span></a>
        
            <li class="{{ Request::is('company/departments*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company.departments.index') }}"><i class="fas fa-building"></i> <span>Manage Departments</span></a>
            </li>

            <li class="{{ Request::is('company/teams*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company.teams.index', ['companyId' => Auth::user()->company_id]) }}"><i class="fas fa-users-cog"></i> <span>Manage Teams</span></a>
            </li>

            <li class="menu-header">Leave Management</li>
            <li class="{{ Request::is('company/leave-types*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company.leave-types.index') }}"><i class="fas fa-calendar-alt"></i> <span>Leave Types</span></a>
            </li>
            <li class="{{ Request::is('company/leave-balances*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company.leave-balances.index') }}"><i class="fas fa-balance-scale"></i> <span>Leave Balances</span></a>
            </li>
            <li class="{{ Request::is('company/leave-requests') && !Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company.leave-requests.index') }}"><i class="fas fa-clipboard-list"></i> <span>Leave Requests</span></a>
            </li>
            <li class="{{ Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('company.leave-requests.calendar') }}"><i class="fas fa-calendar-alt"></i> <span>Leave Calendar</span></a>
            </li>

           
            <li class="menu-header">Reimbursements</li>
          
            <li class="{{ Request::is('reimbursements/create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reimbursements.create') }}"><i class="fas fa-plus-circle"></i> <span>Request Reimbursement</span></a>
            </li>
            <li class="{{ Request::is('reimbursements') && !Request::is('reimbursements/create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reimbursements.index') }}"><i class="fas fa-tasks"></i> <span>Pending Approvals</span></a>
            </li>
         
           
         
            @endif



          
            
            

     



            <!-- profile ganti password -->
            <li class="menu-header">Profile</li>
            <li class="{{ Request::is('profile/edit') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('profile/edit') }}"><i class="far fa-user"></i> <span>Profile</span></a>
            </li>
            <li class="{{ Request::is('profile/change-password') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('profile/change-password') }}"><i class="fas fa-key"></i> <span>Change Password</span></a>
            </li>
           
        </ul>
    </aside>
</div>
@endauth
