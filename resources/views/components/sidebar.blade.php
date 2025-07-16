@auth
    @php
        // Get role directly from users table
        $user = Auth::user();
        $userRole = $user ? $user->role ?? 'employee' : 'employee';
        
        // Get module access for the company
        $moduleAccess = [];
        try {
            if ($user && method_exists($user, 'employee') && $user->employee) {
                $employee = $user->employee;
                if (method_exists($employee, 'company') && $employee->company) {
                    $moduleAccess = \App\Models\ModuleAccess::where('company_id', $employee->company->id)
                        ->where('has_access', true)
                        ->get()
                        ->groupBy('module_name')
                        ->map(function($items) {
                            return $items->pluck('role')->toArray();
                        })
                        ->toArray();
                }
            }
        } catch (\Exception $e) {
            // Log the error but don't break the page
            \Log::error('Error loading module access: ' . $e->getMessage());
            $moduleAccess = [];
        }
        
        // Function to check if a module is accessible for the current user's role
        $hasModuleAccess = function($module, $role = null) use ($moduleAccess, $userRole) {
            $role = $role ?? $userRole;
            
            // Company admin has access to all modules
            if ($role === 'company_admin') {
                return true;
            }
            
            // Check if module exists in access list
            if (!isset($moduleAccess[$module])) {
                return false;
            }
            
            // For admin role, check if admin has access
            if ($role === 'admin') {
                // Admin can only access modules where admin access is explicitly granted
                return in_array('admin', $moduleAccess[$module]);
            }
            
            // For employee role, check if employee has access
            if ($role === 'employee') {
                // Employee can access modules where employee access is explicitly granted
                return in_array('employee', $moduleAccess[$module]);
            }
            
            // Default case (should never reach here)
            return false;
        };
    @endphp
    
    <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
            <!-- Add search input -->
            <div class="sidebar-search px-4 py-2">
                <div class="input-group">
                    <input type="text" class="form-control" id="sidebar-menu-search" placeholder="Search menu...">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>

            <ul class="sidebar-menu">
                <!-- Dashboard -->
                <li class="menu-header">Dashboard</li>
                <li class="{{ Request::is('home') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('home') }}">
                        <i class="fas fa-fire"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Superadmin Routes -->
                @if (Auth::user()->hasRole('superadmin'))
                    <li class="menu-header">Superadmin</li>
                    <li class="{{ Request::is('hakakses') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('hakakses') }}">
                            <i class="fas fa-user-shield"></i>
                            <span>All Users</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/companies') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.companies.index') }}">
                            <i class="fas fa-building"></i>
                            <span>Manage Companies</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/assigned-company-admins') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.assigned-company-admins.index') }}">
                            <i class="fas fa-users-cog"></i>
                            <span>Assign Company Admin</span>
                        </a>
                    </li>
                @endif


                <!-- Employee Routes -->
                @if (Auth::user()->hasRole('employee'))
                 @if($hasModuleAccess('attendance', 'employee'))
                    <li class="{{ Request::is('employee/colleagues') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('employee.colleagues') }}">
                            <i class="fas fa-users"></i>
                            <span>My Colleagues</span>
                        </a>
                    </li>
                    @endif

                    <!-- Attendance Management -->
                    @if($hasModuleAccess('attendance', 'employee'))
                        <li class="menu-header">Attendance Management</li>
                        <li class="{{ Request::is('attendance') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('attendance.dashboard') }}">
                                <i class="fas fa-clock"></i>
                                <span>Attendance Dashboard</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('attendance/check-in-out') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('attendance.check-in') }}">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Check In/Out</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('attendance/my-attendance') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('attendance.my-attendance') }}">
                                <i class="fas fa-calendar-check"></i>
                                <span>My Attendance</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('regularization/requests*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('regularization.requests.index') }}">
                                <i class="fas fa-user-clock"></i>
                                <span>Regularization Requests</span>
                            </a>
                        </li>
                    @endif

                    <!-- Leave Management -->
                    @if($hasModuleAccess('leave', 'employee'))
                        <li class="menu-header">Leave Management</li>
                        <li class="{{ Request::is('leave-management/leave-requests') && !Request::is('leave-management/leave-requests/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-management.leave-requests.index') }}">
                                <i class="fas fa-clipboard-list"></i>
                                <span>My Leave Requests</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('leave-management/leave-requests/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-management.leave-requests.create') }}">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Apply for Leave</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('leave-management/leave-requests/calendar') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-management.leave-requests.calendar') }}">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Leave Calendar</span>
                            </a>
                        </li>
                    @endif

                    <!-- Payroll Management -->
                    @if($hasModuleAccess('payroll', 'employee'))
                        <li class="menu-header">Payroll Management</li>
                        @if(isset(Auth::user()->employee) && Auth::user()->employee->currentSalary)
                            <li class="{{ Request::is('employee/salary/payslips*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('employee.salary.payslips') }}">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>My Payslips</span>
                                </a>
                            </li>
                        @endif
                    @endif

                    <!-- Reimbursements -->
                    @if($hasModuleAccess('reimbursement', 'employee'))
                        <li class="menu-header">Reimbursements</li>
                        <li class="{{ Request::is('reimbursements') && !Request::is('reimbursements/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('reimbursements.index') }}">
                                <i class="fas fa-receipt"></i>
                                <span>My Reimbursements</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('reimbursements/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('reimbursements.create') }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Request Reimbursement</span>
                            </a>
                        </li>
                    @endif                                
@endif

<!-- Company Admin Routes -->
{{-- Company Admin Routes --}}
@if(Auth::user()->hasRole('company_admin'))

    <!-- Holiday Management -->
    <li class="menu-header">Holiday Management</li>
    <li class="{{ Request::is('company/*/academic-holidays*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company.academic-holidays.index', Auth::user()->company_id) }}">
            <i class="fas fa-calendar"></i>
            <span>Academic Holidays</span>
        </a>
    </li>

    <!-- Employee Management -->
    <li class="menu-header">Employee Management</li>
    <li class="{{ Request::is('company-admin/employees*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company-admin.employees.index') }}">
            <i class="fas fa-users"></i>
            <span>Employee Management</span>
        </a>
    </li>

    <!-- Team Management -->
    <li class="menu-header">Team Management</li>
    <li class="{{ Request::is('company-admin/module-access*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company-admin.module-access.index') }}">
            <i class="fas fa-key"></i>
            <span>Module Access</span>
        </a>
    </li>
    <li class="{{ Request::is('company-admin/settings*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company-admin.settings.index') }}">
            <i class="fas fa-cog"></i>
            <span>Company Settings</span>
        </a>
    </li>
    <li class="{{ Request::is('company/designations*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company.designations.index') }}">
            <i class="fas fa-id-badge"></i>
            <span>Manage Designations</span>
        </a>
    </li>
    <li class="{{ Request::is('company/departments*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company.departments.index') }}">
            <i class="fas fa-building"></i>
            <span>Manage Departments</span>
        </a>
    </li>
    <li class="{{ Request::is('company/teams*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company.teams.index', ['companyId' => Auth::user()->company_id]) }}">
            <i class="fas fa-users-cog"></i>
            <span>Manage Teams</span>
        </a>
    </li>

    <!-- Attendance Management -->
    <li class="menu-header">Attendance Management</li>
    <li class="{{ Request::is('regularization/requests*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('regularization.requests.index') }}">
            <i class="fas fa-user-clock"></i>
            <span>Regularization Requests</span>
        </a>
    </li>
    <li class="{{ Request::is('admin/attendance') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.attendance.index') }}">
            <i class="fas fa-user-clock"></i>
            <span>Manage Attendance</span>
        </a>
    </li>
    <li class="{{ Request::is('admin/attendance/summary') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.attendance.summary') }}">
            <i class="fas fa-chart-pie"></i>
            <span>Attendance Summary</span>
        </a>
    </li>
    <li class="{{ Request::is('attendance/check-in-out') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('attendance.check-in') }}"><i class="fas fa-sign-in-alt"></i>
                            <span>Check In/Out</span></a>
        </li>
     <li class="{{ Request::is('attendance/my-attendance') ? 'active' : '' }}">
      <a class="nav-link" href="{{ route('attendance.my-attendance') }}"><i
                                class="fas fa-calendar-check"></i> <span>My Attendance</span></a>
                    </li>
    <li class="{{ Request::is('admin/attendance/settings*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.attendance.settings') }}"><i class="fas fa-cog"></i>
            <span>Attendance Settings</span></a>
                    </li>
     <li class="{{ Request::is('admin/shifts*') ? 'active' : '' }}">
    </li>

    <!-- Leave Management -->
    <li class="menu-header">Leave Management</li>
    <li class="{{ Request::is('company/leave-types*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company.leave-types.index') }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Leave Types</span>
        </a>
    </li>
    <li class="{{ Request::is('company/leave-balances*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company.leave-balances.index') }}">
            <i class="fas fa-balance-scale"></i>
            <span>Leave Balances</span>
        </a>
    </li>
    <li class="{{ Request::is('leave-management/leave-requests/create') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('leave-management.leave-requests.create') }}">
            <i class="fas fa-calendar-plus"></i>
            <span>Apply for Leave</span>
        </a>
    </li>
    <li class="{{ Request::is('company/leave-requests') && !Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company.leave-requests.index') }}">
            <i class="fas fa-clipboard-list"></i>
            <span>Leave Requests</span>
        </a>
    </li>
    <li class="{{ Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('company.leave-requests.calendar') }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Leave Calendar</span>
        </a>
    </li>

    <!-- Payroll Management -->
    <li class="menu-header">Payroll Management</li>
    <li class="nav-item dropdown {{ Request::is('admin/payroll*') ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-file-invoice-dollar"></i><span>Payroll</span></a>
        <ul class="dropdown-menu">
            <li class="{{ Request::routeIs('admin.payroll.create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.payroll.create') }}">Generate Payroll</a>
            </li>
            <li class="{{ Request::routeIs('admin.payroll.index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.payroll.index') }}">View Payrolls</a>
            </li>
            <li class="{{ Request::routeIs('admin.payroll.settings.edit') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.payroll.settings.edit') }}">Payroll Settings</a>
            </li>
            <li class="{{ Request::routeIs('admin.beneficiary-badges.index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.beneficiary-badges.index') }}">Beneficiary Badges</a>
            </li>
            <li class="{{ Request::routeIs('admin.employee-payroll-configurations.index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.employee-payroll-configurations.index') }}">Employee Payroll Configs</a>
            </li>
        </ul>
    </li>

@endif {{-- END of Company Admin role block --}}

{{-- Employee Payslip --}}
@if($hasModuleAccess('payroll', 'employee'))
    @if(isset(Auth::user()->employee) && Auth::user()->employee->currentSalary)
        <li class="{{ Request::is('employee/salary/payslips*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('employee.salary.payslips') }}">
                <i class="fas fa-file-pdf"></i>
                <span>My Payslips</span>
            </a>
        </li>
    @endif
@endif













            <!-- Employee Routes -->
            {{--  Admin Routes --}}
            @if (Auth::user()->hasRole('admin'))
            @if($hasModuleAccess('team', 'admin'))
                    <!-- Holiday Management -->
                    <li class="menu-header">Holiday Management</li>
                    <li class="{{ Request::is('company/*/academic-holidays*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company.academic-holidays.index', Auth::user()->company_id) }}">
                            <i class="fas fa-calendar"></i>
                            <span>Academic Holidays</span>
                        </a>
                    </li>

                    <!-- Employee Management -->
                    <li class="menu-header">Employee Management</li>
                    <li class="{{ Request::is('company-admin/employees*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company-admin.employees.index') }}">
                            <i class="fas fa-users"></i>
                            <span>Employee Management</span>
                        </a>
                    </li>
                    @endif

                    <!-- Team Management -->
                    @if($hasModuleAccess('team', 'admin'))
                        <li class="menu-header">Team Management</li>
                        <li class="{{ Request::is('company-admin/module-access*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company-admin.module-access.index') }}">
                                <i class="fas fa-key"></i>
                                <span>Module Access</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('company-admin/settings*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company-admin.settings.index') }}">
                                <i class="fas fa-cog"></i>
                                <span>Company Settings</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('company/designations*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company.designations.index') }}">
                                <i class="fas fa-id-badge"></i>
                                <span>Manage Designations</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('company/departments*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company.departments.index') }}">
                                <i class="fas fa-building"></i>
                                <span>Manage Departments</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('company/teams*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company.teams.index', ['companyId' => Auth::user()->company_id]) }}">
                                <i class="fas fa-users-cog"></i>
                                <span>Manage Teams</span>
                            </a>
                        </li>
                    @endif

                    <!-- Attendance Management -->
                    @if($hasModuleAccess('attendance', 'admin'))
                        <li class="menu-header">Attendance Management</li>
                        <li class="{{ Request::is('regularization/requests*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('regularization.requests.index') }}">
                                <i class="fas fa-user-clock"></i>
                                <span>Regularization Requests</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('admin/attendance') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.attendance.index') }}">
                                <i class="fas fa-user-clock"></i>
                                <span>Manage Attendance</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('admin/attendance/summary') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.attendance.summary') }}">
                                <i class="fas fa-chart-pie"></i>
                                <span>Attendance Summary</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('attendance/check-in-out') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('attendance.check-in') }}"><i class="fas fa-sign-in-alt"></i>
                            <span>Check In/Out</span></a>
                    </li>
                    <li class="{{ Request::is('attendance/my-attendance') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('attendance.my-attendance') }}"><i
                                class="fas fa-calendar-check"></i> <span>My Attendance</span></a>
                    </li>
                    <li class="{{ Request::is('admin/attendance/settings*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.attendance.settings') }}"><i class="fas fa-cog"></i>
                            <span>Attendance Settings</span></a>
                    </li>
                    <li class="{{ Request::is('admin/shifts*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i>
                            <span>Manage Shifts</span></a>
                    </li>
                    @endif

                     <!-- Leave Management -->
                     @if($hasModuleAccess('leave', 'admin'))
                    <li class="menu-header">Leave Management</li>
                    <li class="{{ Request::is('company/leave-types*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company.leave-types.index') }}"><i
                                class="fas fa-calendar-alt"></i> <span>Leave Types</span></a>
                    </li>
                    <li class="{{ Request::is('company/leave-balances*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company.leave-balances.index') }}"><i
                                class="fas fa-balance-scale"></i> <span>Leave Balances</span></a>
                    </li>
                    <li class="{{ Request::is('leave-management/leave-requests/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('leave-management.leave-requests.create') }}"><i
                                class="fas fa-calendar-plus"></i> <span>Apply for Leave</span></a>
                    </li>
                    <li
                        class="{{ Request::is('company/leave-requests') && !Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company.leave-requests.index') }}"><i
                                class="fas fa-clipboard-list"></i> <span>Leave Requests</span></a>
                    </li>
                    <li class="{{ Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company.leave-requests.calendar') }}"><i
                                class="fas fa-calendar-alt"></i> <span>Leave Calendar</span></a>
                    </li>
                    @endif

                 

                  <!-- Payroll Management -->
                  @if($hasModuleAccess('payroll', 'admin'))
                    <li class="menu-header">Payroll Management
                    <li class="nav-item dropdown {{ Request::is('admin/payroll*') ? 'active' : '' }}">
                        <a href="#" class="nav-link has-dropdown"><i class="fas fa-file-invoice-dollar"></i><span>Payroll</span></a>
                        <ul class="dropdown-menu">
                            <li class="{{ Request::routeIs('admin.payroll.create') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.payroll.create') }}">Generate Payroll</a>
                    </li>
                    <li class="{{ Request::routeIs('admin.payroll.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.payroll.index') }}">View Payrolls</a>
                    </li>
                    <li class="{{ Request::routeIs('admin.payroll.settings.edit') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.payroll.settings.edit') }}">Payroll Settings</a>
                    </li>
                    <li class="{{ Request::routeIs('admin.beneficiary-badges.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.beneficiary-badges.index') }}"> <span>Beneficiary Badges</span></a>
                    </li>
                    <li class="{{ Request::routeIs('admin.employee-payroll-configurations.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.employee-payroll-configurations.index') }}"> <span>Employee Payroll Configs</span></a>
                    </li>
                        </ul>
                    </li>
                    
                    </li>
            </li>
            @endif
            
            <!-- Payslip Management -->
            @if($hasModuleAccess('payroll', 'employee'))
            @if(isset(Auth::user()->employee) && Auth::user()->employee->currentSalary)
            <li class="{{ Request::is('employee/salary/payslips*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('employee.salary.payslips') }}">
                    <i class="fas fa-file-pdf"></i>
                    <span>My Payslips</span>
                </a>
            </li>
            @endif
            @endif

             <!-- reimbursement Management -->
             @if($hasModuleAccess('reimbursement', 'admin'))
            <li class="menu-header">Reimbursements</li>
          
            <li class="{{ Request::is('reimbursements/create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reimbursements.create') }}"><i class="fas fa-plus-circle"></i> <span>Request Reimbursement</span></a>
            </li>
            <li class="{{ Request::is('reimbursements') && !Request::is('reimbursements/create') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('reimbursements.index') }}"><i class="fas fa-tasks"></i> <span>Pending Approvals</span></a>
            </li>
         </li>
            @endif

         @endif









            <li class="menu-header">Profile Management</li>
              <li class="{{ Request::is('profile/edit') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('profile/edit') }}"><i class="far fa-user"></i>
                        <span>Profile</span></a>

                        
           
                <li class="{{ Request::is('profile/change-password') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('profile/change-password') }}"><i class="fas fa-key"></i>
                        <span>Change Password</span></a>
                 </li>


                
                
             
              

            </ul>
        </aside>
    </div>
@endauth
