<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExampleController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DesignationManagementController;
use App\Http\Controllers\DepartmentManagementController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\ReimbursementController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changepassword'])->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/blank-page', [App\Http\Controllers\HomeController::class, 'blank'])->name('blank');

    // Hakakses routes
    Route::middleware(['role'])->group(function () {
        Route::get('/hakakses', [App\Http\Controllers\HakaksesController::class, 'index'])->name('hakakses.index');
        Route::get('/hakakses/{user}/edit', [App\Http\Controllers\HakaksesController::class, 'edit'])->name('hakakses.edit');
        Route::put('/hakakses/{user}', [App\Http\Controllers\HakaksesController::class, 'update'])->name('hakakses.update');
        Route::delete('/hakakses/{user}', [App\Http\Controllers\HakaksesController::class, 'destroy'])->name('hakakses.delete');
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
        Route::resource('companies', SuperAdminController::class)->except(['show']);
    });

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('company')->name('company.')->group(function () {
        // Employee Management
        Route::get('companies/{companyId}/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('companies/{companyId}/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('companies/{companyId}/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('companies/{companyId}/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('companies/{companyId}/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('companies/{companyId}/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        // Designation Management
        Route::resource('designations', DesignationManagementController::class)->except(['show'])->names([
            'index' => 'designations.index',
            'create' => 'designations.create',
            'store' => 'designations.store',
            'edit' => 'designations.edit',
            'update' => 'designations.update',
            'destroy' => 'designations.destroy',
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

    // Employee Routes
    Route::middleware(['role:user,employee'])->prefix('employee')->name('employee.')->group(function () {
        // Profile
        Route::get('profile', [EmployeeController::class, 'show'])->name('profile');
        Route::get('colleagues', [EmployeeController::class, 'listColleagues'])->name('colleagues');

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

    // Company Admin Routes
    Route::middleware(['role:company_admin'])->prefix('company-admin')->name('company-admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\CompanyAdminController::class, 'dashboard'])->name('dashboard');

        // Module Access Management
        Route::get('/module-access', [\App\Http\Controllers\CompanyAdminController::class, 'moduleAccess'])->name('module-access.index');
        Route::put('/module-access', [\App\Http\Controllers\CompanyAdminController::class, 'updateModuleAccess'])->name('module-access.update');

        // Employee Management
        Route::get('/employees', [\App\Http\Controllers\CompanyAdminController::class, 'employees'])->name('employees.index');
        Route::get('/employees/create', [\App\Http\Controllers\CompanyAdminController::class, 'createEmployee'])->name('employees.create');
        Route::post('/employees', [\App\Http\Controllers\CompanyAdminController::class, 'storeEmployee'])->name('employees.store');
        Route::put('/employees/{employee}/role', [\App\Http\Controllers\CompanyAdminController::class, 'updateEmployeeRole'])->name('employees.update-role');

        // Company Settings
        Route::get('/settings', [\App\Http\Controllers\CompanyAdminController::class, 'settings'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\CompanyAdminController::class, 'updateSettings'])->name('settings.update');
    });
}); // End of auth middleware group