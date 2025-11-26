<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExampleController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyDocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\DesignationManagementController;
use App\Http\Controllers\DepartmentManagementController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\AcademicHolidayController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\PayrollController as AdminPayrollController;
use App\Http\Controllers\Employee\PayrollController as EmployeePayrollController;
use App\Http\Controllers\Admin\AttendanceAdjustmentController;
use App\Http\Controllers\Admin\BeneficiaryBadgeController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Admin\EmployeePayrollConfigController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\FieldVisitController;
use App\Http\Controllers\HandbookController;
use App\Http\Controllers\SuperAdmin\DemoRequestsController;
use App\Http\Controllers\SuperAdmin\ContactMessagesController;
use App\Http\Controllers\LeadController;
// Test logging route - can be removed after testing
require __DIR__ . '/test-logging.php';


// Route::get('/migrate-fresh-seed', function () {
//     Artisan::call('migrate:fresh', [
//         '--seed' => true,
//         '--force' => true,
//     ]);

//     return '✅ Fresh migration with seeding done!';
// });
// Route::get('/migrate', function () {
//     Artisan::call('migrate');

//     return '✅ Migration done!';
// });


Route::get('/run-attendance/{date?}', function ($date = null) {
    try {
        // Pass the date as an Artisan option
        Artisan::call('attendance:run-all', [
            'date' => $date,
        ]);

        Log::info('✅ attendance:run-all run from route at ' . now() . ' for date: ' . ($date ?? 'today'));

        return '✅ Attendance command executed for date: ' . ($date ?? 'today');
    } catch (\Exception $e) {
        Log::error('❌ Failed to run attendance command: ' . $e->getMessage());
        return response('❌ Error running attendance command.', 500);
    }
});

Route::prefix('company-admin')->name('company-admin.')->group(function () {
    Route::resource('announcements', AnnouncementController::class)->middleware('auth');
});

Route::get('/', function () {
    return redirect()->route('login');
    // return view('welcome');
});

Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('/dashboard/switch', [App\Http\Controllers\HomeController::class, 'switchDashboard'])->name('dashboard.switch');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changepassword'])->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/blank-page', [App\Http\Controllers\HomeController::class, 'blank'])->name('blank');

    // Hakakses routes
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/hakakses', [App\Http\Controllers\HakaksesController::class, 'index'])->name('hakakses.index');
        Route::get('/hakakses/{user}/edit', [App\Http\Controllers\HakaksesController::class, 'edit'])->name('hakakses.edit');
        Route::put('/hakakses/{user}', [App\Http\Controllers\HakaksesController::class, 'update'])->name('hakakses.update');
        Route::delete('/hakakses/{user}', [App\Http\Controllers\HakaksesController::class, 'destroy'])->name('hakakses.delete');
    });

    // Attendance Management
    // Attendance Regularization
    Route::prefix('regularization')->name('regularization.')->group(function () {
        Route::resource('requests', App\Http\Controllers\Employee\AttendanceRegularizationController::class);
        Route::put('requests/{id}/approve', [App\Http\Controllers\Employee\AttendanceRegularizationController::class, 'approve'])->name('requests.approve');
        Route::post('requests/bulk-update', [App\Http\Controllers\Employee\AttendanceRegularizationController::class, 'bulkUpdate'])->name('requests.bulk-update');
    });

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [EmployeeAttendanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/check-in-out', [EmployeeAttendanceController::class, 'checkInOut'])->name('check-in');
        Route::get('/my-attendance', [EmployeeAttendanceController::class, 'myAttendance'])->name('my-attendance');

        // Export routes
        Route::get('/export', [EmployeeAttendanceController::class, 'exportAttendance'])->name('export');
        Route::get('/export-pdf', [EmployeeAttendanceController::class, 'exportAttendancePdf'])->name('exportPdf');

        // API endpoints for check-in/out
        Route::post('/check-in', [EmployeeAttendanceController::class, 'checkIn'])->name('check-in.post');
        Route::post('/check-out', [EmployeeAttendanceController::class, 'checkOut'])->name('check-out.post');
        Route::get('/summary', [EmployeeAttendanceController::class, 'myAttendanceSummary'])->name('summary');
        Route::get('/check-location', [EmployeeAttendanceController::class, 'checkLocation'])->name('check-location');

        // Get geolocation settings
        Route::get('/geolocation-settings', [EmployeeAttendanceController::class, 'getGeolocationSettings'])
            ->name('geolocation-settings');
    });

    // Employee Payroll Management
    Route::prefix('employee/payroll')->name('employee.payroll.')->group(function () {
        Route::get('/', [EmployeePayrollController::class, 'index'])->name('index'); // List my payslips
        Route::get('/{payroll}', [EmployeePayrollController::class, 'show'])->name('show'); // View a specific payslip
        Route::get('/{payroll}/download', [EmployeePayrollController::class, 'downloadPayslip'])->name('download'); // Download payslip PDF
    });

    // Admin Attendance Management
    Route::middleware(['role:admin,company_admin'])->prefix('admin/attendance')->name('admin.attendance.')->group(function () {

        Route::get('/', [AdminAttendanceController::class, 'index'])->name('index');
        Route::get('/summary', [AdminAttendanceController::class, 'summary'])->name('summary');
        Route::post('/', [AdminAttendanceController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminAttendanceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminAttendanceController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminAttendanceController::class, 'destroy'])->name('destroy');
        Route::post('/import', [AdminAttendanceController::class, 'import'])->name('import');
        Route::get('/import-results', [AdminAttendanceController::class, 'importResults'])->name('import-results');
        Route::get('/export', [AdminAttendanceController::class, 'export'])->name('export');
        Route::get('/template', [AdminAttendanceController::class, 'template'])->name('template');

        // Attendance Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'index'])
            ->name('settings');
        Route::get('/settings/view', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'show'])
            ->name('settings.view');
        Route::match(['post', 'put'], '/settings', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'update'])
            ->name('settings.update');
        Route::get('/api/office-timings', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'getOfficeTimings'])
            ->name('api.office-timings');
    });

    // Admin Regularization Management
    Route::middleware(['role:admin,company_admin'])->prefix('admin/regularization')->name('admin.regularization.')->group(function () {
        Route::resource('requests', \App\Http\Controllers\Employee\AttendanceRegularizationController::class);
        Route::put('requests/{id}/approve', [\App\Http\Controllers\Employee\AttendanceRegularizationController::class, 'approve'])->name('requests.approve');
        Route::post('requests/bulk-update', [\App\Http\Controllers\Employee\AttendanceRegularizationController::class, 'bulkUpdate'])->name('requests.bulk-update');
    });

    // Employee Leave Management
    Route::prefix('leave-management')->name('leave-management.')->group(function () {
        // Leave Requests
        Route::get('leave-requests', [LeaveRequestController::class, 'employeeIndex'])->name('leave-requests.index');
        Route::get('leave-requests/calendar', [LeaveRequestController::class, 'employeeCalendar'])->name('leave-requests.calendar');
        Route::get('leave-requests/calendar-events', [LeaveRequestController::class, 'employeeCalendarEvents'])->name('leave-requests.calendar-events');
        Route::get('leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
        Route::post('leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
        Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
        Route::get('leave-requests/{leaveRequest}/edit', [LeaveRequestController::class, 'edit'])->name('leave-requests.edit');
        Route::put('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('leave-requests.update');
        Route::post('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
        Route::get('leave-requests/export', [LeaveRequestController::class, 'employeeExport'])->name('leave-requests.export');

        // Leave Balances
        Route::get('leave-balances', [LeaveBalanceController::class, 'employeeBalances'])->name('leave-balances.index');
        Route::get('leave-balances/history', [LeaveBalanceController::class, 'history'])->name('leave-balances.history');
    });

    Route::get('/gallery-example', [App\Http\Controllers\ExampleController::class, 'gallery'])->name('gallery.example');
    Route::get('/todo-example', [App\Http\Controllers\ExampleController::class, 'todo'])->name('todo.example');
    Route::get('/contact-example', [App\Http\Controllers\ExampleController::class, 'contact'])->name('contact.example');
    Route::get('/faq-example', [App\Http\Controllers\ExampleController::class, 'faq'])->name('faq.example');
    Route::get('/news-example', [App\Http\Controllers\ExampleController::class, 'news'])->name('news.example');
    Route::get('/about-example', [App\Http\Controllers\ExampleController::class, 'about'])->name('about.example');

    // SuperAdmin Routes (Can manage Companies)
    Route::middleware(['role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::resource('companies', SuperAdminController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show']);
        Route::resource('assign-company-admin', \App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class)->except(['show']);
        Route::get('assigned-company-admins', [\App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class, 'index'])->name('assigned-company-admins.index');
        Route::get('companies/{companyId}/admins', [EmployeeController::class, 'admins'])->name('admins.index');

        // Demo Requests and Contact Messages Views
        Route::get('demo-requests', [DemoRequestsController::class, 'index'])->name('demo-requests.index');
        Route::get('contact-messages', [ContactMessagesController::class, 'index'])->name('contact-messages.index');

        // Company Documents Management
        Route::prefix('companies/{company}/documents')->name('companies.documents.')->group(function () {
            Route::get('/', [CompanyDocumentController::class, 'index'])->name('index');
            Route::post('/upload', [CompanyDocumentController::class, 'upload'])->name('upload');
            Route::get('/{document}', [CompanyDocumentController::class, 'show'])->name('show');
            Route::delete('/{document}', [CompanyDocumentController::class, 'destroy'])->name('destroy');
            Route::post('/{document}/verify', [CompanyDocumentController::class, 'verify'])->name('verify');
            Route::post('/{document}/reject', [CompanyDocumentController::class, 'reject'])->name('reject');
        });

        // Company and Employee Deactivation
        Route::post('companies/{id}/deactivate', [SuperAdminController::class, 'deactivateCompany'])->name('companies.deactivate');
        Route::post('companies/{id}/activate', [SuperAdminController::class, 'activateCompany'])->name('companies.activate');
        Route::post('companies/{companyId}/employees/{employeeId}/deactivate', [SuperAdminController::class, 'deactivateEmployee'])->name('companies.employees.deactivate');
        Route::post('companies/{companyId}/employees/{employeeId}/activate', [SuperAdminController::class, 'activateEmployee'])->name('companies.employees.activate');
    });

    // Shift Management
    Route::middleware(['auth', 'role:admin,company_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('shifts', '\App\Http\Controllers\Admin\ShiftController');

        // Additional shift routes
        Route::get('shifts/{shift}/assign', '\App\Http\Controllers\Admin\ShiftController@showAssignForm')
            ->name('shifts.assign.show');
        Route::post('shifts/{shift}/assign', '\App\Http\Controllers\Admin\ShiftController@assignShift')
            ->name('shifts.assign');

        // Salary Management
        // Route::get('salary', [\App\Http\Controllers\Admin\EmployeeSalaryController::class, 'index'])->name('salary.index');
        // Route::get('salary/create', [\App\Http\Controllers\Admin\EmployeeSalaryController::class, 'create'])->name('salary.create');
        // Route::post('salary', [\App\Http\Controllers\Admin\EmployeeSalaryController::class, 'store'])->name('salary.store');
        // Route::get('salary/{employee}/edit', [\App\Http\Controllers\Admin\EmployeeSalaryController::class, 'edit'])->name('salary.edit');
        // Route::put('salary/{employee}', [\App\Http\Controllers\Admin\EmployeeSalaryController::class, 'update'])->name('salary.update');
        // Route::get('salary/{employee}/show', [\App\Http\Controllers\Admin\EmployeeSalaryController::class, 'show'])->name('salary.show');
        // Route::delete('salary/{employee}', [\App\Http\Controllers\Admin\EmployeeSalaryController::class, 'destroy'])->name('salary.destroy');

        // Asset Management Routes
        // Asset Categories
        Route::resource('assets-categories', AssetCategoryController::class)->names([
            'index' => 'assets.categories.index',
            'create' => 'assets.categories.create',
            'store' => 'assets.categories.store',
            'show' => 'assets.categories.show',
            'edit' => 'assets.categories.edit',
            'update' => 'assets.categories.update',
            'destroy' => 'assets.categories.destroy',
        ]);
        Route::prefix('assets')->name('assets.')->group(function () {
            // Asset routes
            Route::get('/', [AssetController::class, 'index'])->name('index');
            Route::get('/create', [AssetController::class, 'create'])->name('create');
            Route::post('/', [AssetController::class, 'store'])->name('store');
            Route::get('/show/{asset}', [AssetController::class, 'show'])->name('show');
            Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
            Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
            Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');

            // Asset Assignments, distinct from assets route by naming convention and URL structure
            Route::prefix('assignments')->name('assignments.')->group(function () {
                Route::get('/', [AssetAssignmentController::class, 'index'])->name('index');
                Route::get('/create', [AssetAssignmentController::class, 'create'])->name('create');
                Route::post('/', [AssetAssignmentController::class, 'store'])->name('store');
                Route::get('/show/{assignment}', [AssetAssignmentController::class, 'show'])->name('show');
                Route::get('/{assignment}/edit', [AssetAssignmentController::class, 'edit'])->name('edit');
                Route::put('/{assignment}', [AssetAssignmentController::class, 'update'])->name('update');
                Route::delete('/{assignment}', [AssetAssignmentController::class, 'destroy'])->name('destroy');
                Route::post('/{assignment}/return', [AssetAssignmentController::class, 'returnAsset'])->name('return');
            });
        });
    });

    // Admin Payroll Management
    Route::middleware(['role:admin,company_admin'])->prefix('admin/payroll')->name('admin.payroll.')->group(function () {
        Route::get('/', [AdminPayrollController::class, 'index'])->name('index'); // List all payrolls, or payrolls for a company

        // Beneficiary Badges
        Route::resource('beneficiary-badges', BeneficiaryBadgeController::class)->except(['show']);
        Route::get('beneficiary-badges/{beneficiary_badge}', [BeneficiaryBadgeController::class, 'show'])->name('beneficiary-badges.show');
        Route::post('beneficiary-badges/{beneficiary_badge}/apply-to-all', [BeneficiaryBadgeController::class, 'applyToAllEmployees'])->name('beneficiary-badges.apply-to-all');
        Route::post('beneficiary-badges/{beneficiary_badge}/api/apply-to-all', [BeneficiaryBadgeController::class, 'apiApplyToAllEmployees'])->name('beneficiary-badges.api.apply-to-all');

        // Payroll Settings
        Route::get('settings', [App\Http\Controllers\Admin\PayrollSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [App\Http\Controllers\Admin\PayrollSettingsController::class, 'update'])->name('settings.update');
        Route::get('/create', [AdminPayrollController::class, 'create'])->name('create'); // Show form to select employees/period for payroll generation
        Route::post('/', [AdminPayrollController::class, 'store'])->name('store'); // Process payroll generation
        Route::get('/{payroll}', [AdminPayrollController::class, 'show'])->name('show'); // View a specific payroll details
        Route::get('/{payroll}/edit', [AdminPayrollController::class, 'edit'])->name('edit'); // Edit a payroll (e.g., adjustments before processing)
        Route::put('/{payroll}', [AdminPayrollController::class, 'update'])->name('update');
        Route::patch('/{payroll}/process', [AdminPayrollController::class, 'processPayroll'])->name('process'); // Mark as processed
        Route::patch('/{payroll}/mark-as-paid', [AdminPayrollController::class, 'markAsPaid'])->name('mark-as-paid'); // Mark as paid
        Route::patch('/{payroll}/mark-as-paid', [AdminPayrollController::class, 'markAsPaid'])->name('markAsPaid'); // Mark as paid (alternative name)
        Route::patch('/{payroll}/cancel', [AdminPayrollController::class, 'cancel'])->name('cancel'); // Cancel a payroll run
        Route::delete('/{payroll}', [AdminPayrollController::class, 'destroy'])->name('destroy'); // Delete/cancel a payroll run
        Route::post('/bulk-approve', [AdminPayrollController::class, 'bulkApprove'])->name('bulk-approve'); // Bulk approve payrolls
        Route::post('/bulk-approve', [AdminPayrollController::class, 'bulkApprove'])->name('bulkApprove'); // Bulk approve payrolls (alternative name)
        // e.g., Route::get('/reports', [AdminPayrollController::class, 'reports'])->name('reports');

        // Beneficiary Badges Management (Allowances/Deductions)
        Route::resource('beneficiary-badges', BeneficiaryBadgeController::class);

        // Employee Payroll Configuration (CTC, Badges)
        Route::get('employee-configurations', [EmployeePayrollConfigController::class, 'index'])->name('employee-configurations.index');
        Route::get('employee-configurations/{employee}/edit', [EmployeePayrollConfigController::class, 'edit'])->name('employee-configurations.edit');
        Route::put('employee-configurations/{employee}', [EmployeePayrollConfigController::class, 'update'])->name('employee-configurations.update');
        // Set current salary for an employee (with optional employeeSalary parameter)
        Route::put('employee-configurations/{employee}/set-current/{employeeSalary?}', [EmployeePayrollConfigController::class, 'setCurrent'])->name('employee-configurations.set-current');

        // Create new salary for employee
        Route::post('employee-configurations/{employee}/create-salary', [EmployeePayrollConfigController::class, 'createSalary'])->name('employee-configurations.create-salary');
    });

    // Admin Beneficiary Badges Management
    Route::middleware(['role:admin,company_admin'])
        ->prefix('admin/beneficiary-badges')
        ->name('admin.beneficiary-badges.') // Ensured trailing dot for consistency
        ->group(function () {
            Route::resource('/', App\Http\Controllers\Admin\BeneficiaryBadgeController::class)
                ->parameters(['' => 'beneficiary_badge']); // Removed explicit ->names() to use Laravel's default resource naming with group prefix

            // Apply badge to all employees
            Route::post('/{beneficiary_badge}/apply-to-all', [App\Http\Controllers\Admin\BeneficiaryBadgeController::class, 'applyToAllEmployees'])
                ->name('apply-to-all');

            // API endpoint for applying badge to all employees (AJAX)
            Route::post('/{beneficiary_badge}/api/apply-to-all', [App\Http\Controllers\Admin\BeneficiaryBadgeController::class, 'apiApplyToAllEmployees'])
                ->name('api.apply-to-all');
        });

    // Admin Employee Payroll Configurations Management
    Route::middleware(['role:admin,company_admin'])->prefix('admin/employee-payroll-configurations')->name('admin.employee-payroll-configurations.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\EmployeePayrollConfigController::class, 'index'])->name('index');
        Route::get('/{employee}/edit', [App\Http\Controllers\Admin\EmployeePayrollConfigController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [App\Http\Controllers\Admin\EmployeePayrollConfigController::class, 'update'])->name('update');
        Route::put('/{employee}/update-salary', [App\Http\Controllers\Admin\EmployeePayrollConfigController::class, 'updateSalary'])->name('update-salary');
        // Add other resource routes (create, store, show, destroy) here later if needed
    });

    // Route::get('company/academic-holidays', [AcademicHolidayController::class, 'index'])->name('company.academic-holidays.index');

    Route::middleware(['auth', 'role:admin,company_admin,employee', 'ensure.company'])->prefix('company')->name('company.')->group(function () {
        // Academic Holidays Management
        Route::get('academic-holidays', [AcademicHolidayController::class, 'index'])->name('academic-holidays.index');
        Route::get('academic-holidays/create', [AcademicHolidayController::class, 'create'])->name('academic-holidays.create');
        Route::post('academic-holidays', [AcademicHolidayController::class, 'store'])->name('academic-holidays.store');
        Route::get('academic-holidays/{holiday}/edit', [AcademicHolidayController::class, 'edit'])->name('academic-holidays.edit');
        Route::put('academic-holidays/{holiday}', [AcademicHolidayController::class, 'update'])->name('academic-holidays.update');
        Route::delete('academic-holidays/{holiday}', [AcademicHolidayController::class, 'destroy'])->name('academic-holidays.destroy');
        Route::post('academic-holidays/import', [AcademicHolidayController::class, 'import'])->name('academic-holidays.import');
        Route::get('academic-holidays/template', [AcademicHolidayController::class, 'downloadTemplate'])->name('academic-holidays.template');

        // Employee Management
        Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');

        // Admin Management for Company
        Route::get('admins', [EmployeeController::class, 'admins'])->name('admins.index');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        // Designation Management
        Route::resource('designations', DesignationManagementController::class)->except(['show'])->names([
            'index' => 'designations.index',
            'create' => 'designations.create',
            'store' => 'designations.store',
            'edit' => 'designations.edit',
            'update' => 'designations.update',
            'destroy' => 'designations.destroy',
        ]);

        // Employment Type Management
        Route::resource('employment-types', \App\Http\Controllers\EmploymentTypeManagementController::class)->except(['show', 'destroy'])->names([
            'index' => 'employment-types.index',
            'create' => 'employment-types.create',
            'store' => 'employment-types.store',
            'edit' => 'employment-types.edit',
            'update' => 'employment-types.update',
        ]);

        // Department Management
        Route::resource('departments', DepartmentManagementController::class)->except(['show'])->names([
            'index' => 'departments.index',
            'create' => 'departments.create',
            'store' => 'departments.store',
            'edit' => 'departments.edit',
            'update' => 'departments.update',
            'destroy' => 'departments.destroy',
        ]);

        // Team Management
        Route::get('departments/{department}/employees', [TeamController::class, 'getEmployeesByDepartment'])->name('departments.employees');
        Route::resource('teams', TeamController::class)->except(['show']);

        // Leave Management
        Route::resource('leave-types', LeaveTypeController::class);

        // Leave Requests
        Route::get('leave-requests', [LeaveRequestController::class, 'adminIndex'])->name('leave-requests.index');
        Route::get('leave-requests/calendar', [LeaveRequestController::class, 'adminCalendar'])->name('leave-requests.calendar');
        Route::get('leave-requests/create', [LeaveRequestController::class, 'adminCreate'])->name('leave-requests.create');
        Route::post('leave-requests', [LeaveRequestController::class, 'adminStore'])->name('leave-requests.store');
        Route::get('leave-requests/calendar-events', [LeaveRequestController::class, 'adminCalendarEvents'])->name('leave-requests.calendar-events');
        Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'adminShow'])->name('leave-requests.show');
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
        Route::get('leave-requests/export', [LeaveRequestController::class, 'export'])->name('leave-requests.export');
        Route::get('leave-requests/report', [LeaveRequestController::class, 'report'])->name('leave-requests.report');

        // Leave Balances
        Route::resource('leave-balances', LeaveBalanceController::class)->except(['show', 'destroy']);
        Route::post('leave-balances/bulk-allocate', [LeaveBalanceController::class, 'bulkAllocate'])->name('leave-balances.bulk-allocate');
        Route::post('leave-balances/reset', [LeaveBalanceController::class, 'resetBalances'])->name('leave-balances.reset');
        Route::get('leave-balances/export', [LeaveBalanceController::class, 'export'])->name('leave-balances.export');

    });



    // Debug route for attendance data
    Route::get('/debug/attendance', function () {
        $user = \App\Models\User::first();
        $employee = $user->employee;
        $month = now()->format('Y-m');

        $attendances = $employee->attendances()
            ->whereYear('date', '=', date('Y', strtotime($month)))
            ->whereMonth('date', '=', date('m', strtotime($month)))
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'employee_id' => $employee->id,
            'month' => $month,
            'total_attendances' => $attendances->count(),
            'attendances' => $attendances->map(function ($att) {
                return [
                    'date' => $att->date,
                    'status' => $att->status,
                    'check_in' => $att->check_in,
                    'check_out' => $att->check_out
                ];
            })
        ]);
    });

    // Employee Routes
    Route::middleware(['role:user,employee'])->prefix('employee')->name('employee.')->group(function () {
        // Profile
        Route::get('profile', [\App\Http\Controllers\Employee\ProfileController::class, 'show'])->name('profile');
        Route::post('profile/update', [\App\Http\Controllers\Employee\ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/update-image', [\App\Http\Controllers\Employee\ProfileController::class, 'updateImage'])->name('profile.update-image');
        Route::get('colleagues', [EmployeeController::class, 'listColleagues'])->name('colleagues');

        // Assets
        Route::get('assets', [AssetController::class, 'employeeAssets'])->name('assets.index');
        // Salary Routes
        Route::prefix('salary')->name('salary.')->group(function () {
            // Route::get('details', [\App\Http\Controllers\Employee\SalaryController::class, 'details'])->name('details');
            // Route::get('monthly/{year}/{month}', [\App\Http\Controllers\Employee\SalaryController::class, 'monthlyDetails'])
            //     ->where(['year' => '[0-9]{4}', 'month' => '0[1-9]|1[0-2]'])
            //     ->name('monthly.details');

            // PDF Payslip Routes
            Route::get('payslips', [\App\Http\Controllers\PayslipController::class, 'listPayslips'])->name('payslips');

            Route::get('payslip/{employee}/{monthYear?}', [\App\Http\Controllers\PayslipController::class, 'showPayslip'])
                ->where('monthYear', '[0-9]{4}-(0[1-9]|1[0-2])')
                ->name('payslip.view');

            Route::get('payslip/{employee}/{monthYear}/download/{salaryId?}', [\App\Http\Controllers\PayslipController::class, 'downloadPayslip'])
                ->where('monthYear', '[0-9]{4}-(0[1-9]|1[0-2])')
                ->where('salaryId', '[0-9]*')
                ->name('payslip.download');
        });

        // Leave Requests
        Route::get('leave-requests', [LeaveRequestController::class, 'employeeIndex'])->name('leave-requests.index');
        Route::get('leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
        Route::post('leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
        Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
        Route::get('leave-requests/edit/{leaveRequest}', [LeaveRequestController::class, 'edit'])->name('leave-requests.edit');
        Route::put('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('leave-requests.update');
        Route::post('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
        Route::get('leave-requests/calendar', [LeaveRequestController::class, 'employeeCalendar'])->name('leave-requests.calendar');
        Route::get('leave-requests/calendar-events', [LeaveRequestController::class, 'employeeCalendarEvents'])->name('leave-requests.calendar-events');

        // Leave Balances
        Route::get('leave-balances', [LeaveBalanceController::class, 'employeeBalances'])->name('leave-balances.index');
        Route::get('leave-balances/history', [LeaveBalanceController::class, 'history'])->name('leave-balances.history');
    });

    // Reimbursement Routes
    Route::prefix('reimbursements')->name('reimbursements.')->group(function () {
        Route::get('/', [ReimbursementController::class, 'index'])->name('index');
        Route::get('/create', [ReimbursementController::class, 'create'])->name('create');
        Route::post('/', [ReimbursementController::class, 'store'])->name('store');
        Route::get('/{reimbursement}', [ReimbursementController::class, 'show'])->name('show');
        Route::post('/{reimbursement}/approve', [ReimbursementController::class, 'approve'])->name('approve');
        Route::post('/{reimbursement}/approve/reporter', [ReimbursementController::class, 'approveReporter'])->name('approve.reporter');
        Route::post('/{reimbursement}/reject', [ReimbursementController::class, 'reject'])->name('reject');
        Route::get('/pending', [ReimbursementController::class, 'pending'])->name('pending');
    });

    // Company Admin Routes (accessible to company_admin and admin roles)
    Route::middleware(['role:admin,company_admin'])->prefix('company-admin')->name('company-admin.')->group(function () {
        // Leads Management
        Route::resource('leads', LeadController::class);

        // Payslips Management
        Route::get('/payslips', [\App\Http\Controllers\PayslipController::class, 'getAllPayslips'])
            ->name('payslips.index');

        // Export Payslips
        Route::get('/payslips/export', [\App\Http\Controllers\PayslipController::class, 'exportPayslips'])
            ->name('payslips.export');

        // Module Access Management
        Route::get('/module-access', [\App\Http\Controllers\CompanyAdminController::class, 'moduleAccess'])->name('module-access.index');
        Route::put('/module-access', [\App\Http\Controllers\CompanyAdminController::class, 'updateModuleAccess'])->name('module-access.update');

        // Employee Management
        Route::get('/employees', [\App\Http\Controllers\CompanyAdminController::class, 'employees'])->name('employees.index');
        Route::get('/employees/create', [\App\Http\Controllers\CompanyAdminController::class, 'createEmployee'])->name('employees.create');
        Route::post('/employees', [\App\Http\Controllers\CompanyAdminController::class, 'storeEmployee'])->name('employees.store');
        Route::get('/employees/{id}/view', [\App\Http\Controllers\CompanyAdminController::class, 'viewEmployee'])->name('employees.view');
        Route::get('/employees/{id}/edit', [\App\Http\Controllers\CompanyAdminController::class, 'editEmployee'])->name('employees.edit');
        Route::put('/employees/{id}', [\App\Http\Controllers\CompanyAdminController::class, 'updateEmployee'])->name('employees.update');
        Route::put('/employees/{employee}/role', [\App\Http\Controllers\CompanyAdminController::class, 'updateEmployeeRole'])->name('employees.update-role');
        Route::post('/employees/{id}/toggle-status', [\App\Http\Controllers\CompanyAdminController::class, 'toggleStatus'])->name('employees.toggleStatus');

        // Company Settings
        Route::get('/settings', [\App\Http\Controllers\CompanyAdminController::class, 'settings'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\CompanyAdminController::class, 'updateSettings'])->name('settings.update');

        // Employee ID Prefix Settings Save
        Route::post('/settings/save-employee-id-prefix', [\App\Http\Controllers\CompanyAdminController::class, 'saveEmployeeIdPrefix'])->name('settings.save-employee-id-prefix');

        // Employee code generation for company-admin (AJAX)
        Route::get('/employees/next-code', [\App\Http\Controllers\EmployeeController::class, 'getNextEmployeeCode'])->name('employees.next-code');

        // Asset Dashboard
        Route::get('/assets/dashboard', [\App\Http\Controllers\CompanyAdminController::class, 'assetDashboard'])->name('assets.dashboard');
        Route::get('/assets/inventory', [\App\Http\Controllers\CompanyAdminController::class, 'assetInventory'])->name('assets.inventory');
        Route::get('/assets/employees', [\App\Http\Controllers\CompanyAdminController::class, 'employeesWithAssets'])->name('assets.employees');
        Route::get('/assets/assignments', [\App\Http\Controllers\CompanyAdminController::class, 'recentAssignments'])->name('assets.assignments');

    });

    // Employee Resignation Routes
    Route::middleware(['role:user,employee'])->prefix('employee')->name('employee.')->group(function () {
        Route::resource('resignations', App\Http\Controllers\Employee\ResignationController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::post('resignations/{resignation}/withdraw', [App\Http\Controllers\Employee\ResignationController::class, 'withdraw'])
            ->name('resignations.withdraw');
    });

    // Admin Resignation Routes
    Route::middleware(['role:admin,company_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('resignations', App\Http\Controllers\Admin\ResignationController::class)
            ->only(['index', 'show']);
        Route::post('resignations/{resignation}/approve', [App\Http\Controllers\Admin\ResignationController::class, 'approve'])
            ->name('resignations.approve');
        Route::post('resignations/{resignation}/reject', [App\Http\Controllers\Admin\ResignationController::class, 'reject'])
            ->name('resignations.reject');

        // Exit process management
        Route::post('resignations/{resignation}/complete-exit-interview', [App\Http\Controllers\Admin\ResignationController::class, 'completeExitInterview'])
            ->name('resignations.complete-exit-interview');
        Route::post('resignations/{resignation}/complete-handover', [App\Http\Controllers\Admin\ResignationController::class, 'completeHandover'])
            ->name('resignations.complete-handover');
        Route::get('resignations/{resignation}/assigned-assets', [App\Http\Controllers\Admin\ResignationController::class, 'getAssignedAssets'])
            ->name('resignations.assigned-assets');
        Route::post('resignations/{resignation}/mark-assets-returned', [App\Http\Controllers\Admin\ResignationController::class, 'markAssetsReturned'])
            ->name('resignations.mark-assets-returned');
        Route::post('resignations/{resignation}/complete-final-settlement', [App\Http\Controllers\Admin\ResignationController::class, 'completeFinalSettlement'])
            ->name('resignations.complete-final-settlement');
    });


    Route::middleware(['auth'])->group(function () {
        Route::get('/field-visits/pending', [FieldVisitController::class, 'pendingApprovals'])->name('field-visits.pending');
        Route::resource('field-visits', FieldVisitController::class)->except(['store']);
        Route::post('/field-visits', [FieldVisitController::class, 'store'])->name('field-visits.store');
        Route::post('/field-visits/{fieldVisit}/approve', [FieldVisitController::class, 'approve'])->name('field-visits.approve');
        Route::post('/field-visits/{fieldVisit}/reject', [FieldVisitController::class, 'reject'])->name('field-visits.reject');
        Route::post('/field-visits/{fieldVisit}/start', [FieldVisitController::class, 'start'])->name('field-visits.start');
        Route::post('/field-visits/{fieldVisit}/complete', [FieldVisitController::class, 'complete'])->name('field-visits.complete');
    });
    Route::middleware(['auth'])->group(function () {
        Route::resource('handbooks', HandbookController::class);
        Route::get('handbooks/{handbook}/download', [HandbookController::class, 'download'])->name('handbooks.download');
        Route::post('handbooks/{handbook}/acknowledge', [HandbookController::class, 'acknowledge'])->name('handbooks.acknowledge');
    });
    Route::middleware(['auth'])->group(function () {
        Route::resource('handbooks', HandbookController::class);
        Route::post('handbooks/{handbook}/acknowledge', [HandbookController::class, 'acknowledge'])->name('handbooks.acknowledge');
    });

}); // End of auth middleware group
