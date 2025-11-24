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
                        ->map(function ($items) {
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
        $hasModuleAccess = function ($module, $role = null) use ($moduleAccess, $userRole) {
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
            <div class="sidebar-search px-4 pt-5">
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
                <li class="menu-header pt-0">Dashboard</li>
                <li class="menu-item {{ Request::is('home') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('home') }}">
                        <i class="fas fa-fire"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Asset Management -->
                @if (Auth::user()->hasRole('company_admin') || Auth::user()->hasRole('admin'))
                    <li class="menu-header">Asset Management</li>

                    <li class="{{ Request::is('company-admin/assets/dashboard') ? 'active' : '' }}">
                       <a class="nav-link" href="{{ route('assets.dashboard') }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Asset Dashboard</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('assets-categories*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('assets.categories.index') }}">
                            <i class="fas fa-tags"></i>
                            <span>Asset Categories</span>
                        </a>
                    </li>
                    <li
                        class="{{ (Request::is('assets/*') || Request::is('assets')) && !Request::is('assets/assignments*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('assets.index') }}">
                            <i class="fas fa-laptop"></i>
                            <span>Assets</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('assets/assignments*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('assets.assignments.index') }}">
                            <i class="fas fa-hand-holding"></i>
                            <span>Asset Assignments</span>
                        </a>

                    </li>
                    
                    {{-- <li class="{{ Request::is('company-admin/assets/inventory') ? 'active' : '' }}">
                       <a class="nav-link" href="{{ route('company-admin.assets.inventory') }}">
                            <i class="fas fa-boxes"></i>
                            <span>Asset Inventory</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('company-admin/assets/employees') ? 'active' : '' }}">
                       <a class="nav-link" href="{{ route('company-admin.assets.employees') }}">
                            <i class="fas fa-user-tag"></i>
                            <span>Employees with Assets</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('company-admin/assets/assignments') ? 'active' : '' }}">
                       <a class="nav-link" href="{{ route('company-admin.assets.assignments') }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Asset Assignments</span>
                        </a>
                    </li> --}}
                @endif

                {{-- <li class="{{ Request::is('admin/assets') && !Request::is('admin/assets/*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('assets.index') }}">
                        <i class="fas fa-laptop"></i>
                        <span>Assets</span>
                    </a>
                </li> --}}


                <!-- End Asset Management -->

                <!-- Superadmin Routes -->
                @if (Auth::user()->hasRole('superadmin'))
                    <li class="menu-header">Superadmin</li>
                    <li class="{{ Request::is('superadmin/user/*') || Request::is('superadmin/users') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('superadmin/users') }}">
                            <i class="fas fa-user-shield"></i>
                            <span>All Users</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/companies*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.companies.index') }}">
                            <i class="fas fa-building"></i>
                            <span>Manage Companies</span>
                        </a>
                    </li>
                    <li
                        class="{{ Request::is('superadmin/assigned-company-admins*') || Request::is('superadmin/assign-company-admin/*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.assigned-company-admins.index') }}">
                            <i class="fas fa-users-cog"></i>
                            <span>Assign Company Admin</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/setting/slugs*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.setting.slugs') }}">
                            <i class="fas fa-link"></i>
                            <span>Manage Slugs</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/roles*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.roles.index') }}">
                            <i class="fas fa-user-tag"></i>
                            <span>Manage Roles</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/demo-requests') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.demo-requests.index') }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Demo Requests</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/contact-messages') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.contact-messages.index') }}">
                            <i class="fas fa-envelope"></i>
                            <span>Contact Messages</span>
                        </a>
                    </li>

                    <!-- Package Management -->
                    {{-- <li class="menu-header">Package Management</li>
                    <li class="{{ Request::is('superadmin/packages*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.packages.index') }}">
                            <i class="fas fa-box"></i>
                            <span>Packages</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/company-packages*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.company-packages.index') }}">
                            <i class="fas fa-handshake"></i>
                            <span>Company Packages</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/discounts*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.discounts.index') }}">
                            <i class="fas fa-percent"></i>
                            <span>Discounts</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/taxes*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.taxes.index') }}">
                            <i class="fas fa-calculator"></i>
                            <span>Taxes</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('superadmin/invoices*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('superadmin.invoices.index') }}">
                            <i class="fas fa-file-invoice"></i>
                            <span>Invoices</span>
                        </a>
                    </li> --}}
                @endif


                <!-- Employee Routes -->
                @if (Auth::user()->hasRole('employee'))
                    @if ($hasModuleAccess('attendance', 'employee'))
                        <li class="{{ Request::is('employee/colleagues') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('employee.colleagues') }}">
                                <i class="fas fa-users"></i>
                                <span>My Colleagues</span>
                            </a>
                        </li>
                    @endif

                    <!-- Attendance Management -->
                    @if ($hasModuleAccess('attendance', 'employee'))
                        <li class="menu-header">Attendance Management</li>
                        <li class="menu-item {{ Request::is('attendance') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('home') }}">
                                <i class="fas fa-calendar-check"></i>
                                <span>Attendance Dashboard</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('check-in-out') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('check-in-out') }}">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Check In/Out</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('attendance/my-attendance') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('my-attendance') }}">
                                <i class="fas fa-calendar-check"></i>
                                <span>My Attendance</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('regularization/requests*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('regularization-requests.index') }}">
                                <i class="fas fa-user-clock"></i>
                                <span>Regularization Requests</span>
                            </a>
                        </li>
                    @endif

                    <!-- Leave Management -->
                    @if ($hasModuleAccess('leave', 'employee'))
                        <li class="menu-header">Leave Management</li>
                        <li
                            class="menu-item {{ Request::is('leave-requests') && !Request::is('leave-requests/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-requests.index') }}">
                                <i class="fas fa-plane-departure"></i>
                                <span>My Leave Requests</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('leave-requests/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-requests.create') }}">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Apply for Leave</span>
                            </a>
                        </li>
                        {{-- <li class="{{ Request::is('leave-management/leave-requests/calendar') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-management.leave-requests.calendar') }}">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Leave Calendar</span>
                            </a>
                        </li> --}}
                    @endif

                    <!-- Payroll Management -->
                    @if ($hasModuleAccess('payroll', 'employee'))
                        <li class="menu-header">Payroll Management</li>
                        @if (isset(Auth::user()->employee) && Auth::user()->employee->currentSalary)
                            <li class="{{ Request::is('employee/salary/payslips*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('employee.salary.payslips') }}">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>My Payslips</span>
                                </a>
                            </li>
                        @endif
                    @endif

                    <!-- Reimbursements -->
                    @if ($hasModuleAccess('reimbursement', 'employee'))
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

                    <!-- Field Visits -->
                    <li class="menu-header">Field Visits</li>
                    <li
                        class="{{ Request::is('field-visits') && !Request::is('field-visits/create') && !Request::is('field-visits/pending') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('field-visits.index') }}">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>My Field Visits</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('field-visits/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('field-visits.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            <span>Schedule Visit</span>
                        </a>
                    </li>

                    <!-- Assets -->
                    <li class="menu-header">Assets</li>
                    <li class="{{ Request::is('employee/assets') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('employee.assets.index') }}">
                            <i class="fas fa-laptop"></i>
                            <span>My Assets</span>
                        </a>
                    </li>

                    @if (Auth::user()->hasRole('employee'))
                        <li class="menu-header">Holiday Management</li>
                        <li class="{{ Request::is('academic-holidays') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('academic-holidays.index') }}">
                                <i class="fas fa-calendar"></i>
                                <span>Academic Holidays</span>
                            </a>
                        </li>
                    @endif

                    <!-- Resignations -->
                    <li class="menu-header">Resignations</li>
                    <li
                        class="{{ Request::is('resignations') && !Request::is('resignations/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('resignations.index') }}">
                            <i class="fas fa-file-signature"></i>
                            <span>My Resignations</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('resignations/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('resignations.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            <span>Submit Resignation</span>
                        </a>
                    </li>
                @endif

                <!-- Company employee management -->


                {{-- Company Admin Routes --}}
                @if (Auth::user()->hasRole('company_admin'))
                    <!-- Holiday Management -->
                    <li class="menu-header">Holiday Management</li>
                    <li class="{{ Request::is('academic-holidays') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('academic-holidays.index') }}">
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
                    <li class="{{ Request::is('admin/resignations') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.resignations.index') }}">
                            <i class="fas fa-file-signature"></i>
                            <span>Employee Resignations</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('company-admin/announcements') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('announcements.index') }}">
                            <i class="fas fa-bullhorn"></i>
                            <span>Announcements</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('company-admin/announcements/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('announcements.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            <span>Create Announcement</span>
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
                    <li class="{{ Request::is('company-admin/roles*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company-admin.roles.index') }}">
                            <i class="fas fa-user-tag"></i>
                            <span>Role Management</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('company-admin/settings*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company-admin.settings.index') }}">
                            <i class="fas fa-cog"></i>
                            <span>Company Settings</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('departments*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('departments.index') }}">
                            <i class="fas fa-building"></i>
                            <span>Manage Departments</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('designations*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('designations.index') }}">
                            <i class="fas fa-id-badge"></i>
                            <span>Manage Designations</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('company/employment-types*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('employment-types.index') }}">
                            <i class="fas fa-briefcase"></i>
                            <span>Employment Types</span>
                        </a>
                    </li>
                    {{-- <li class="{{ Request::is('company/teams*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company.teams.index') }}">
                            <i class="fas fa-users-cog"></i>
                            <span>Manage Teams</span>
                        </a>
                    </li> --}}

                    <!-- Attendance Management -->
                    <li class="menu-header">Attendance Management</li>
                    <li class="{{ Request::is('regularization/requests*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('regularization-requests.index') }}">
                            <i class="fas fa-user-clock"></i>
                            <span>Regularization Requests</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('admin/attendance') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin-attendance.index') }}">
                            <i class="fas fa-user-clock"></i>
                            <span>Manage Attendance</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('admin/attendance/summary') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin-attendance.summary') }}">
                            <i class="fas fa-chart-pie"></i>
                            <span>Attendance Summary</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('check-in-out') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('check-in-out') }}"><i class="fas fa-sign-in-alt"></i>
                            <span>Check In/Out</span></a>
                    </li>
                    <li class="{{ Request::is('my-attendance') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('my-attendance') }}"><i class="fas fa-calendar-check"></i>
                            <span>My Attendance</span></a>
                    </li>
                    <li class="{{ Request::is('admin-attendance/settings*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin-attendance.settings') }}"><i class="fas fa-cog"></i>
                            <span>Attendance Settings</span></a>
                    </li>
                    <li class="{{ Request::is('admin/shifts*') ? 'active' : '' }}">
                    </li>

                    <!-- Leave Management -->
                    <li class="menu-header">Leave Management</li>
                    <li class="{{ Request::is('leave-types*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('leave-types.index') }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Leave Types</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('leave-balances*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('leave-balances.index') }}">
                            <i class="fas fa-balance-scale"></i>
                            <span>Leave Balances</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('leave-requests/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('leave-requests.create') }}">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Apply for Leave</span>
                            </i>
                        </a>
                    </li>
                    <li
                        class="{{ Request::is('company/leave-requests') && !Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('leave-requests.index') }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Leave Requests</span>
                        </a>
                    </li>

                    <li class="menu-header">Leads Management</li>
                    <li class="{{ Request::is('company-admin/leads') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company-admin.leads.index') }}">
                            <i class="fas fa-user-plus"></i>
                            <span>Leads</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('company-admin/leads/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company-admin.leads.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Lead</span>
                        </a>
                    </li>
                    {{-- <li class="{{ Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company.leave-requests.calendar') }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Leave Calendar</span>
                        </a>
                    </li> --}}

                    <!-- Payroll Management -->
                    <li class="menu-header">Payroll Management</li>
                    <li class="nav-item dropdown {{ Request::is('admin/payroll*') ? 'active' : '' }}">
                        <a href="#" class="nav-link has-dropdown"><i
                                class="fas fa-file-invoice-dollar"></i><span>Payroll</span></a>
                        <ul class="dropdown-menu">
                            <li class="{{ Request::routeIs('admin.payroll.create') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.payroll.create') }}">Generate Payroll</a>
                            </li>
                            <li class="{{ Request::routeIs('admin.payroll.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.payroll.index') }}">View Payrolls</a>
                            </li>
                            <li class="{{ Request::routeIs('admin.payroll.settings.edit') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.payroll.settings.edit') }}">Payroll
                                    Settings</a>
                            </li>
                            <li class="{{ Request::routeIs('admin.beneficiary-badges.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.beneficiary-badges.index') }}">Beneficiary
                                    Badges</a>
                            </li>
                            <li class="{{ Request::routeIs('admin.employee-payroll-configurations.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.employee-payroll-configurations.index') }}">Employee
                                    Payroll
                                    Configs</a>
                            </li>
                        </ul>
                    </li>

                    <!-- reimbursement Management -->
                    @if ($hasModuleAccess('reimbursement', 'admin'))
                        <li class="menu-header">Reimbursements</li>

                        <li class="{{ Request::is('reimbursements/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('reimbursements.create') }}"><i class="fas fa-plus-circle"></i>
                                <span>Request Reimbursement</span></a>
                        </li>
                        <li class="{{ Request::is('reimbursements') && !Request::is('reimbursements/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('reimbursements.index') }}"><i class="fas fa-tasks"></i>
                                <span>Pending Approvals</span></a>
                        </li>
                        </li>
                    @endif

                    <!-- Field Visits -->
                    <li class="menu-header">Field Visits</li>
                    <li
                        class="{{ Request::is('field-visits') && !Request::is('field-visits/create') && !Request::is('field-visits/pending') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('field-visits.index') }}">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Field Visits</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('field-visits/pending') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('field-visits.pending') }}">
                            <i class="fas fa-clock"></i>
                            <span>Pending Approvals</span>
                        </a>
                    </li>
                @endif {{-- END of Company Admin role block --}}

                {{-- Employee Payslip --}}
                {{-- @if ($hasModuleAccess('payroll', 'employee'))
                @if (isset(Auth::user()->employee) && Auth::user()->employee->currentSalary)
                <li class="{{ Request::is('employee/salary/payslips*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('employee.salary.payslips') }}">
                        <i class="fas fa-file-pdf"></i>
                        <span>My Payslips</span>
                    </a>
                </li>
                @endif
                @endif --}}

                <!-- Employee Routes -->
                {{-- Admin Routes --}}
                @if (Auth::user()->hasRole('admin'))
                    @if ($hasModuleAccess('team', 'admin'))
                        <!-- Holiday Management -->
                        <li class="menu-header">Holiday Management </li>
                        <li class="{{ Request::is('academic-holidays') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('academic-holidays.index') }}">
                                <i class="fas fa-calendar"></i>
                                <span>Academic Holidays </span>
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
                        <!-- Resignation Management -->
                        <li class="{{ Request::is('admin/resignations') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.resignations.index') }}">
                                <i class="fas fa-file-signature"></i>
                                <span>Employee Resignations</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('company-admin/announcements') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('announcements.index') }}">
                                <i class="fas fa-bullhorn"></i>
                                <span>Announcements</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('company-admin/announcements/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('announcements.create') }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Create Announcement</span>
                            </a>
                        </li>
                    @endif

                    <!-- Team Holiday Management -->
                    @if (Auth::user()->hasRole('user'))
                        @if ($hasModuleAccess('team'))
                            <li class="menu-header">Team Holiday Management</li>
                            <li class="{{ Request::is('company-admin/team-holidays*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('company-admin.team-holidays.index') }}">
                                    <i class="fas fa-calendar"></i>
                                    <span>Team Holidays</span>
                                </a>
                            </li>
                        @endif
                    @endif
                    <!-- Team Management -->
                    @if ($hasModuleAccess('team', 'admin'))
                        <li class="menu-header">Team Management</li>
                        <li class="{{ Request::is('company-admin/module-access*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company-admin.module-access.index') }}">
                                <i class="fas fa-key"></i>
                                <span>Module Access</span>
                            </a>
                        </li>

                        </a>
                        </li>
                        <li class="{{ Request::is('company-admin/settings*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company-admin.settings.index') }}">
                                <i class="fas fa-cog"></i>
                                <span>Company Settings</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('departments*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('departments.index') }}">
                                <i class="fas fa-building"></i>
                                <span>Manage Departments</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('designations*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('designations.index') }}">
                                <i class="fas fa-id-badge"></i>
                                <span>Manage Designations</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('company/employment-types*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('employment-types.index') }}">
                                <i class="fas fa-briefcase"></i>
                                <span>Employment Types</span>
                            </a>
                        </li>
                        {{-- <li class="{{ Request::is('company/teams*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company.teams.index') }}">
                                <i class="fas fa-users-cog"></i>
                                <span>Manage Teams</span>
                            </a>
                        </li> --}}
                    @endif

                    <!-- Attendance Management -->
                    @if ($hasModuleAccess('attendance', 'admin'))
                        <li class="menu-header">Attendance Management</li>
                        <li class="{{ Request::is('regularization/requests*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('regularization-requests.index') }}">
                                <i class="fas fa-user-clock"></i>
                                <span>Regularization Requests</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('admin-attendance') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-attendance.index') }}">
                                <i class="fas fa-user-clock"></i>
                                <span>Manage Attendance</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('admin-attendance/summary') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-attendance.summary') }}">
                                <i class="fas fa-chart-pie"></i>
                                <span>Attendance Summary</span>
                            </a>
                        </li>
                        <li class="{{ Request::is('check-in-out') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('check-in-out') }}"><i class="fas fa-sign-in-alt"></i>
                                <span>Check In/Out</span></a>
                        </li>
                        <li class="{{ Request::is('my-attendance') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('my-attendance') }}"><i class="fas fa-calendar-check"></i>
                                <span>My Attendance</span></a>
                        </li>
                        <li class="{{ Request::is('admin-attendance/settings*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-attendance.settings') }}"><i class="fas fa-cog"></i>
                                <span>Attendance Settings</span></a>
                        </li>
                        {{-- <li class="{{ Request::is('admin/shifts*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i>
                                <span>Manage Shifts</span></a>
                        </li> --}}
                    @endif

                    <!-- Leave Management -->
                    @if ($hasModuleAccess('leave', 'admin'))
                        <li class="menu-header">Leave Management</li>
                        <li class="{{ Request::is('leave-types*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-types.index') }}"><i class="fas fa-calendar-alt"></i>
                                <span>Leave Types</span></a>
                        </li>
                        <li class="{{ Request::is('leave-balances*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-balances.index') }}"><i
                                    class="fas fa-balance-scale"></i> <span>Leave Balances</span></a>
                        </li>
                        <li class="{{ Request::is('leave-requests/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-requests.create') }}"><i
                                    class="fas fa-calendar-plus"></i> <span>Apply for Leave</span></a>
                        </li>
                        <li
                            class="{{ Request::is('leave-requests') && !Request::is('leave-requests/calendar') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('leave-requests.index') }}"><i
                                    class="fas fa-clipboard-list"></i> <span>Leave Requests</span></a>
                        </li>
                        {{-- <li class="{{ Request::is('company/leave-requests/calendar') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('company.leave-requests.calendar') }}"><i
                                    class="fas fa-calendar-alt"></i> <span>Leave Calendar</span></a>
                        </li> --}}
                    @endif

                    <li class="menu-header">Leads Management</li>
                    <li class="{{ Request::is('company-admin/leads') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company-admin.leads.index') }}">
                            <i class="fas fa-user-plus"></i>
                            <span>Leads</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('company-admin/leads/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('company-admin.leads.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Lead</span>
                        </a>
                    </li>



                    <!-- Payroll Management -->
                    @if ($hasModuleAccess('payroll', 'admin'))
                        <li class="menu-header">Payroll Management</li>
                        <li class="nav-item dropdown {{ Request::is('admin/payroll*') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown"><i
                                    class="fas fa-file-invoice-dollar"></i><span>Payroll</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ Request::routeIs('admin.payroll.create') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.payroll.create') }}">Generate Payroll</a>
                                </li>
                                <li class="{{ Request::routeIs('admin.payroll.index') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.payroll.index') }}">View Payrolls</a>
                                </li>
                                <li class="{{ Request::routeIs('admin.payroll.settings.edit') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.payroll.settings.edit') }}">Payroll
                                        Settings</a>
                                </li>
                                <li class="{{ Request::routeIs('admin.beneficiary-badges.index') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beneficiary-badges.index') }}">
                                        <span>Beneficiary Badges</span></a>
                                </li>
                                <li class="{{ Request::routeIs('admin.employee-payroll-configurations.index') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.employee-payroll-configurations.index') }}">
                                        <span>Employee
                                            Payroll Configs</span></a>
                                </li>
                            </ul>
                        </li>
                        </li>
                    @endif

                    <!-- Payslip Management -->
                    @if ($hasModuleAccess('payroll', 'employee'))
                        @if (isset(Auth::user()->employee) && Auth::user()->employee->currentSalary)
                            <li class="{{ Request::is('employee/salary/payslips*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('employee.salary.payslips') }}">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>My Payslips</span>
                                </a>
                            </li>
                        @endif
                    @endif

                    <!-- reimbursement Management -->
                    @if ($hasModuleAccess('reimbursement', 'admin'))
                        <li class="menu-header">Reimbursements</li>

                        <li class="{{ Request::is('reimbursements/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('reimbursements.create') }}"><i class="fas fa-plus-circle"></i>
                                <span>Request Reimbursement</span></a>
                        </li>
                        <li class="{{ Request::is('reimbursements') && !Request::is('reimbursements/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('reimbursements.index') }}"><i class="fas fa-tasks"></i>
                                <span>Pending Approvals</span></a>
                        </li>
                        </li>
                    @endif

                    <!-- Field Visits -->
                    <li class="menu-header">Field Visits</li>
                    <li
                        class="{{ Request::is('field-visits') && !Request::is('field-visits/create') && !Request::is('field-visits/pending') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('field-visits.index') }}">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Field Visits</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('field-visits/pending') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('field-visits.pending') }}">
                            <i class="fas fa-clock"></i>
                            <span>Pending Approvals</span>
                        </a>
                    </li>

                @endif

                <!-- HR Handbooks -->
                <li class="menu-header">HR Handbooks</li>
                <li class="{{ Request::is('handbooks') && !Request::is('handbooks/create') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('handbooks.index') }}">
                        <i class="fas fa-book"></i>
                        <span>Handbooks</span>
                    </a>
                </li>
                @if (Auth::user()->hasRole(['admin', 'company_admin']))
                    <li class="{{ Request::is('handbooks/create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('handbooks.create') }}">
                            <i class="fas fa-plus-circle"></i>
                            <span>Create Handbook</span>
                        </a>
                    </li>
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