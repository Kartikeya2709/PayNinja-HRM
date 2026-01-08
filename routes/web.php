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
use App\Http\Controllers\Employee\AttendanceRegularizationController;
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
use App\Http\Controllers\Admin\BeneficiaryBadgeController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Admin\EmployeePayrollConfigController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\FieldVisitController;
use App\Http\Controllers\SuperAdmin\SlugController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\HandbookController;
use App\Http\Controllers\Employee\ResignationController;
use App\Http\Controllers\SuperAdmin\DemoRequestsController;
use App\Http\Controllers\SuperAdmin\ContactMessagesController;
use App\Http\Controllers\SuperAdmin\DatabaseController;
use App\Http\Controllers\SuperAdmin\LogsController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CompanyPackageController;
use App\Http\Controllers\PackagePricingController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\InvoiceController;

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

Route::get('/', function () {
    return redirect()->route('login');
    // return view('welcome');
});

Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {

    // =============================================
    // DASHBOARD & PROFILE ROUTES
    // =============================================
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/blank-page', [App\Http\Controllers\HomeController::class, 'blank'])->name('blank');
    Route::post('/dashboard/switch', [HomeController::class, 'switchDashboard'])->name('dashboard.switch');

    // Profile Management
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changepassword'])->name('profile.change-password');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    // =============================================
    // SUPER ADMIN ROUTES
    // =============================================
    Route::middleware(['superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

        // User Management
        Route::get('/users', [App\Http\Controllers\HakaksesController::class, 'index'])->name('users.index');
        Route::get('/user/{user}/edit', [App\Http\Controllers\HakaksesController::class, 'edit'])->name('users.edit');
        Route::put('/user/{user}', [App\Http\Controllers\HakaksesController::class, 'update'])->name('users.update');
        Route::delete('/user/{user}', [App\Http\Controllers\HakaksesController::class, 'destroy'])->name('users.delete');

        // *** COMPANY MANAGEMENT ***
        Route::get('companies-list', [SuperAdminController::class, 'index'])->name('companies.index');
        Route::get('company-create', [SuperAdminController::class, 'create'])->name('companies.create');
        Route::post('company-store', [SuperAdminController::class, 'store'])->name('companies.store');
        Route::get('company-show/{company}', [SuperAdminController::class, 'show'])->name('companies.show');
        Route::get('company-edit/{company}', [SuperAdminController::class, 'edit'])->name('companies.edit');
        Route::put('company-update/{company}', [SuperAdminController::class, 'update'])->name('companies.update');
        Route::delete('company-delete/{company}', [SuperAdminController::class, 'destroy'])->name('companies.destroy');
        Route::post('companies/{id}/deactivate', [SuperAdminController::class, 'deactivateCompany'])->name('companies.deactivate');
        Route::post('companies/{id}/activate', [SuperAdminController::class, 'activateCompany'])->name('companies.activate');
        Route::post('companies/{companyId}/employees/{employeeId}/deactivate', [SuperAdminController::class, 'deactivateEmployee'])->name('companies.employees.deactivate');
        Route::post('companies/{companyId}/employees/{employeeId}/activate', [SuperAdminController::class, 'activateEmployee'])->name('companies.employees.activate');

        // Company Admin Assignment
        Route::get('assign-company-admins-list', [\App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class, 'index'])->name('assign-company-admin.index');
        Route::get('assign-company-admin-create', [\App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class, 'create'])->name('assign-company-admin.create');
        Route::post('assign-company-admin-store', [\App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class, 'store'])->name('assign-company-admin.store');
        Route::get('assign-company-admin-edit/{assign_company_admin}', [\App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class, 'edit'])->name('assign-company-admin.edit');
        Route::put('assign-company-admin-update/{assign_company_admin}', [\App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class, 'update'])->name('assign-company-admin.update');
        Route::delete('assign-company-admin-delete/{assign_company_admin}', [\App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class, 'destroy'])->name('assign-company-admin.destroy');
        Route::get('assigned-company-admins', [\App\Http\Controllers\SuperAdmin\AssignCompanyAdminController::class, 'index'])->name('assigned-company-admins.index');

        // Company Documents Management
        Route::prefix('companies/{company}/documents')->name('companies.documents.')->group(function () {
            Route::get('/', [CompanyDocumentController::class, 'index'])->name('index');
            Route::post('/upload', [CompanyDocumentController::class, 'upload'])->name('upload');
            Route::get('/{document}', [CompanyDocumentController::class, 'show'])->name('show');
            Route::delete('/{document}', [CompanyDocumentController::class, 'destroy'])->name('destroy');
            Route::post('/{document}/verify', [CompanyDocumentController::class, 'verify'])->name('verify');
            Route::post('/{document}/reject', [CompanyDocumentController::class, 'reject'])->name('reject');
        });

        // *** ROLE MANAGEMENT ***
        Route::get('superadmin-roles-list', [RoleController::class, 'index'])->name('roles.index');
        Route::get('superadmin-role-create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('superadmin-role-store', [RoleController::class, 'store'])->name('roles.store');
        Route::get('superadmin-role-show/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::get('superadmin-role-edit/{role}', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('superadmin-role-update/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('superadmin-role-delete/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        // *** SLUG MANAGEMENT ***
        Route::get('setting/slugs', [SlugController::class, 'slugs'])->name('setting.slugs');
        Route::post('setting/slug/add', [SlugController::class, 'add_slug'])->name('setting.slug.add');
        Route::match(['get', 'post'], 'setting/slug/edit/{id}', [SlugController::class, 'edit_slug'])->name('setting.slug.edit');
        Route::get('setting/slug/destroy/{id}', [SlugController::class, 'destroy_slug'])->name('setting.slug.destroy');

        // *** PACKAGE MANAGEMENT ***
        Route::get('packages-list', [PackageController::class, 'index'])->name('packages.index');
        Route::get('package-create', [PackageController::class, 'create'])->name('packages.create');
        Route::post('package-store', [PackageController::class, 'store'])->name('packages.store');
        Route::get('package-show/{package}', [PackageController::class, 'show'])->name('packages.show');
        Route::get('package-edit/{package}', [PackageController::class, 'edit'])->name('packages.edit');
        Route::put('package-update/{package}', [PackageController::class, 'update'])->name('packages.update');
        Route::delete('package-delete/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('packages/{package}/toggle-active', [PackageController::class, 'toggleActive'])->name('packages.toggle-active');
        Route::get('packages/{package}/modules', [PackageController::class, 'getModules'])->name('packages.modules');

        // Company Package Assignment
        Route::prefix('company-packages')->name('company-packages.')->group(function () {
            Route::get('/', [CompanyPackageController::class, 'index'])->name('index');
            Route::get('/view/{companyPackage}', [CompanyPackageController::class, 'show'])->name('show');
            Route::get('/assign', [CompanyPackageController::class, 'create'])->name('assign.form');
            Route::post('/assign', [CompanyPackageController::class, 'assign'])->name('assign');
            Route::get('/edit/{id}', [CompanyPackageController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CompanyPackageController::class, 'update'])->name('update');
            Route::delete('/unassign/{id}', [CompanyPackageController::class, 'unassign'])->name('unassign');
            Route::post('/{id}/toggle-active', [CompanyPackageController::class, 'toggleActive'])->name('toggle-active');
            Route::get('/get-company-packages', [CompanyPackageController::class, 'getCompanyPackages'])->name('get-company-packages');
            Route::post('/bulk-assign', [CompanyPackageController::class, 'bulkAssign'])->name('bulk-assign');
            Route::get('/validate-permission-state/{companyId}', [CompanyPackageController::class, 'validatePermissionState'])->name('validate-permission-state');
        });

        // Pricing Tiers
        Route::post('packages/{package}/pricing-tiers', [PackagePricingController::class, 'storeTier'])->name('packages.pricing-tiers.store');
        Route::put('pricing-tiers/{tier}', [PackagePricingController::class, 'updateTier'])->name('pricing-tiers.update');
        Route::delete('pricing-tiers/{tier}', [PackagePricingController::class, 'deleteTier'])->name('pricing-tiers.destroy');
        Route::post('packages/{package}/calculate-price', [PackagePricingController::class, 'calculatePrice'])->name('packages.calculate-price');

        // *** DISCOUNT MANAGEMENT ***
        Route::get('discounts-list', [DiscountController::class, 'index'])->name('discounts.index');
        Route::get('discount-create', [DiscountController::class, 'create'])->name('discounts.create');
        Route::post('discount-store', [DiscountController::class, 'store'])->name('discounts.store');
        Route::get('discount-show/{discount}', [DiscountController::class, 'show'])->name('discounts.show');
        Route::get('discount-edit/{discount}', [DiscountController::class, 'edit'])->name('discounts.edit');
        Route::put('discount-update/{discount}', [DiscountController::class, 'update'])->name('discounts.update');
        Route::delete('discount-delete/{discount}', [DiscountController::class, 'destroy'])->name('discounts.destroy');
        Route::post('discounts/{discount}/validate-code', [DiscountController::class, 'validateCode'])->name('discounts.validate-code');

        // *** TAX MANAGEMENT ***
        Route::get('taxes-list', [TaxController::class, 'index'])->name('taxes.index');
        Route::get('tax-create', [TaxController::class, 'create'])->name('taxes.create');
        Route::post('tax-store', [TaxController::class, 'store'])->name('taxes.store');
        Route::get('tax-show/{tax}', [TaxController::class, 'show'])->name('taxes.show');
        Route::get('tax-edit/{tax}', [TaxController::class, 'edit'])->name('taxes.edit');
        Route::put('tax-update/{tax}', [TaxController::class, 'update'])->name('taxes.update');
        Route::delete('tax-delete/{tax}', [TaxController::class, 'destroy'])->name('taxes.destroy');

        // *** INVOICE MANAGEMENT ***
        Route::get('invoices-list', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoice-create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoice-store', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoice-show/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoice-edit/{invoice}', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('invoice-update/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('invoice-delete/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('invoices/{invoice}/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::post('invoices/{invoice}/send-invoice', [InvoiceController::class, 'sendInvoice'])->name('invoices.send-invoice');

        // *** DEMO & CONTACT MANAGEMENT ***
        Route::get('demo-requests', [DemoRequestsController::class, 'index'])->name('demo-requests.index');
        Route::get('contact-messages', [ContactMessagesController::class, 'index'])->name('contact-messages.index');

        // *** DATABASE MANAGEMENT ***
        Route::get('database', [DatabaseController::class, 'index'])->name('database.index');
        Route::match(['get', 'post'], 'database/table/{table}', [DatabaseController::class, 'showTable'])->name('database.show');
        Route::get('database/table/{table}/export', [DatabaseController::class, 'exportTable'])->name('database.export-table');
        Route::get('database/export', [DatabaseController::class, 'exportDatabase'])->name('database.export');
        Route::match(['get', 'post'], 'database/query', [DatabaseController::class, 'query'])->name('database.query');
        Route::post('database/query', [DatabaseController::class, 'executeQuery'])->name('database.execute-query');

        // *** LOGS MANAGEMENT ***
        Route::get('logs', [LogsController::class, 'index'])->name('logs.index');
        Route::get('logs/{filename}', [LogsController::class, 'show'])->name('logs.show');
        Route::get('logs/{filename}/download', [LogsController::class, 'download'])->name('logs.download');
    });

// Route::middleware('auth')->group(function () {
    Route::middleware('checkAccess')->group(function () {
    // =============================================
    // ANNOUNCEMENTS
    // =============================================
    Route::get('announcements-list', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcement-create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('announcement-store', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('announcement-show/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('announcement-edit/{announcement}', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('announcement-update/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('announcement-delete/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // =============================================
    // ATTENDANCE MANAGEMENT
    // =============================================
    Route::prefix('attendance-management')->group(function () {

        // *** ATTENDANCE REGULARIZATION ***
        Route::get('regularization-requests-list', [AttendanceRegularizationController::class, 'index'])->name('regularization-requests.index');
        Route::get('regularization-requests/my', [AttendanceRegularizationController::class, 'myRequests'])->name('regularization-requests.my');
        Route::get('regularization-requests/company', [AttendanceRegularizationController::class, 'companyRequests'])->name('regularization-requests.company');
        Route::get('regularization-request-create', [AttendanceRegularizationController::class, 'create'])->name('regularization-requests.create');
        Route::post('regularization-request-store', [AttendanceRegularizationController::class, 'store'])->name('regularization-requests.store');
        Route::get('regularization-request-show/{encryptedId}', [AttendanceRegularizationController::class, 'show'])->name('regularization-requests.show');
        Route::get('regularization-request-edit/{encryptedId}', [AttendanceRegularizationController::class, 'edit'])->name('regularization-requests.edit');
        Route::put('regularization-request-update/{encryptedId}', [AttendanceRegularizationController::class, 'update'])->name('regularization-requests.update');
        Route::delete('regularization-request-delete/{encryptedId}', [AttendanceRegularizationController::class, 'destroy'])->name('regularization-requests.destroy');
        Route::put('regularization-requests/{id}/approve', [AttendanceRegularizationController::class, 'approve'])->name('regularization-requests.approve');
        Route::post('regularization-requests/bulk-update', [AttendanceRegularizationController::class, 'bulkUpdate'])->name('regularization-requests.bulk-update');

        // *** ADMIN ATTENDANCE MANAGEMENT ***
        Route::prefix('attendance')->name('admin-attendance.')->group(function () {
            Route::get('/', [AdminAttendanceController::class, 'index'])->name('index');
            Route::get('/summary', [AdminAttendanceController::class, 'summary'])->name('summary');
            Route::post('/store', [AdminAttendanceController::class, 'store'])->name('store');
            Route::get('/{encryptedId}/edit', [AdminAttendanceController::class, 'edit'])->name('edit');
            Route::put('/{encryptedId}/update', [AdminAttendanceController::class, 'update'])->name('update');
            Route::delete('/{encryptedId}/delete', [AdminAttendanceController::class, 'destroy'])->name('destroy');
            Route::post('/import', [AdminAttendanceController::class, 'import'])->name('import');
            Route::get('/import-results', [AdminAttendanceController::class, 'importResults'])->name('import-results');
            Route::get('/export', [AdminAttendanceController::class, 'export'])->name('export');
            Route::get('/template', [AdminAttendanceController::class, 'template'])->name('template');

            // Attendance Settings
            Route::get('/settings', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'index'])->name('settings');
            Route::get('/settings/view', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'show'])->name('settings.view');
            Route::match(['post', 'put'], '/settings', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'update'])->name('settings.update');
            // Route::post('/settings/post', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'update'])->name('settings.update');
            Route::put('/settings/put', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'update'])->name('settings.update');
            Route::get('/api/office-timings', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'getOfficeTimings'])->name('api.office-timings');
        });

        Route::get('/attendance/{encryptedId}/show', [EmployeeAttendanceController::class, 'show'])->name('attendance.show');
        Route::get('/attendance/dashboard', [EmployeeAttendanceController::class, 'dashboard'])->name('attendance.dashboard');
        Route::get('/attendance/check-in-out', [EmployeeAttendanceController::class, 'checkInOut'])->name('check-in-out');
        Route::get('/attendance/my-attendance', [EmployeeAttendanceController::class, 'myAttendance'])->name('my-attendance');
        Route::get('/attendance/my-attendance/export', [EmployeeAttendanceController::class, 'exportAttendance'])->name('my-attendance.export');
        Route::get('/attendance/my-attendance/export-pdf', [EmployeeAttendanceController::class, 'exportAttendancePdf'])->name('my-attendance.exportPdf');

        // Check-in/out API endpoints
        Route::post('/attendance/check-in', [EmployeeAttendanceController::class, 'checkIn'])->name('check-in.post')->middleware('check.attendance.access');
        Route::post('/attendance/check-out', [EmployeeAttendanceController::class, 'checkOut'])->name('check-out.post')->middleware('check.attendance.access');
        Route::get('/attendance/check-location', [EmployeeAttendanceController::class, 'checkLocation'])->name('check-location')->middleware('check.attendance.access');
        Route::get('/geolocation-settings', [EmployeeAttendanceController::class, 'getGeolocationSettings'])->name('geolocation-settings');
    });

    // =============================================
    // LEAVE MANAGEMENT
    // =============================================
    Route::prefix('leaves')->name('leaves.')->group(function () {

        // *** LEAVE TYPES ***
        Route::get('leave-types-list', [LeaveTypeController::class, 'index'])->name('leave-types.index');
        Route::get('leave-type-create', [LeaveTypeController::class, 'create'])->name('leave-types.create');
        Route::post('leave-type-store', [LeaveTypeController::class, 'store'])->name('leave-types.store');
        Route::get('leave-type-show/{leave_type}', [LeaveTypeController::class, 'show'])->name('leave-types.show');
        Route::get('leave-type-edit/{encryptedId}', [LeaveTypeController::class, 'edit'])->name('leave-types.edit');
        Route::put('leave-type-update/{encryptedId}', [LeaveTypeController::class, 'update'])->name('leave-types.update');
        Route::delete('leave-type-delete/{encryptedId}', [LeaveTypeController::class, 'destroy'])->name('leave-types.destroy');

        // *** LEAVE BALANCES ***
        Route::get('leave-balances-list', [LeaveBalanceController::class, 'index'])->name('leave-balances.index');
        Route::get('leave-balance-create', [LeaveBalanceController::class, 'create'])->name('leave-balances.create');
        Route::post('leave-balance-store', [LeaveBalanceController::class, 'store'])->name('leave-balances.store');
        Route::get('leave-balance-edit/{encryptedId}', [LeaveBalanceController::class, 'edit'])->name('leave-balances.edit');
        Route::put('leave-balance-update/{encryptedId}', [LeaveBalanceController::class, 'update'])->name('leave-balances.update');
        Route::post('leave-balances/bulk-allocate', [LeaveBalanceController::class, 'bulkAllocate'])->name('leave-balances.bulk-allocate');
        Route::post('leave-balances/reset', [LeaveBalanceController::class, 'resetBalances'])->name('leave-balances.reset');
        Route::get('leave-balances/export', [LeaveBalanceController::class, 'export'])->name('leave-balances.export');

        // *** ADMIN LEAVE REQUESTS ***
        Route::get('leave-requests', [LeaveRequestController::class, 'adminIndex'])->name('leave-requests.index');
        Route::get('leave-requests/calendar', [LeaveRequestController::class, 'adminCalendar'])->name('leave-requests.calendar');
        Route::get('leave-requests/create', [LeaveRequestController::class, 'adminCreate'])->name('leave-requests.create');
        Route::post('leave-requests/store', [LeaveRequestController::class, 'adminStore'])->name('leave-requests.store');
        Route::get('leave-requests/{encryptedId}', [LeaveRequestController::class, 'adminShow'])->name('leave-requests.show');
        Route::post('leave-requests/{encryptedId}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::post('leave-requests/{encryptedId}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');

        //not corrected yet
        Route::get('leave-requests/export', [LeaveRequestController::class, 'export'])->name('leave-requests.export');
        Route::get('leave-requests/report', [LeaveRequestController::class, 'report'])->name('leave-requests.report');

        // *** EMPLOYEE LEAVE REQUESTS (My Leaves) ***
        Route::prefix('my-leaves')->name('my-leaves.')->group(function () {
            Route::get('my-leave-requests-list', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
            Route::get('my-leave-request-create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
            Route::post('my-leave-request-store', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
            Route::get('my-leave-request-show/{encryptedId}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
            Route::get('my-leave-request-edit/{encryptedId}', [LeaveRequestController::class, 'edit'])->name('leave-requests.edit');
            Route::put('my-leave-request-update/{encryptedId}', [LeaveRequestController::class, 'update'])->name('leave-requests.update');
            Route::post('leave-requests/{encryptedId}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
            // Route::get('leave-requests/calendar', [LeaveRequestController::class, 'employeeCalendar'])->name('leave-requests.calendar');
            // Route::get('leave-requests/calendar-events', [LeaveRequestController::class, 'employeeCalendarEvents'])->name('leaves.my-leaves.leave-requests.calendar-events');
            // Route::get('leave-requests/export', [LeaveRequestController::class, 'employeeExport'])->name('leave-requests.export');
            // Route::get('leave-balances', [LeaveBalanceController::class, 'employeeBalances'])->name('leave-balances.index');
            // Route::get('leave-balances/history', [LeaveBalanceController::class, 'history'])->name('leave-balances.history');
        });
    });

    // Employee Leave Management (Alternative prefix)
    // Route::prefix('leave-management')->name('leave-management.')->group(function () {
    //     Route::get('leave-requests', [LeaveRequestController::class, 'employeeIndex'])->name('leave-requests.index');
    //     Route::get('leave-requests/calendar', [LeaveRequestController::class, 'employeeCalendar'])->name('leave-requests.calendar');
    //     Route::get('leave-requests/calendar-events', [LeaveRequestController::class, 'employeeCalendarEvents'])->name('leave-requests.calendar-events');
    //     Route::get('leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    //     Route::post('leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    //     Route::get('leave-requests/{leaveRequest}/view', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    //     Route::get('leave-requests/{leaveRequest}/edit', [LeaveRequestController::class, 'edit'])->name('leave-requests.edit');
    //     Route::put('leave-requests/{leaveRequest}/update', [LeaveRequestController::class, 'update'])->name('leave-requests.update');
    //     Route::post('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
    //     Route::get('leave-requests/export', [LeaveRequestController::class, 'employeeExport'])->name('leave-requests.export');
    //     Route::get('leave-balances', [LeaveBalanceController::class, 'employeeBalances'])->name('leave-balances.index');
    //     Route::get('leave-balances/history', [LeaveBalanceController::class, 'history'])->name('leave-balances.history');
    // });

    // =============================================
    // DESIGNATION & ORGANIZATIONAL MANAGEMENT
    // =============================================
    Route::get('designations-list', [DesignationManagementController::class, 'index'])->name('designations.index');
    Route::get('designation-create', [DesignationManagementController::class, 'create'])->name('designations.create');
    Route::post('designation-store', [DesignationManagementController::class, 'store'])->name('designations.store');
    Route::get('designation-edit/{encryptedId}', [DesignationManagementController::class, 'edit'])->name('designations.edit');
    Route::put('designation-update/{encryptedId}', [DesignationManagementController::class, 'update'])->name('designations.update');
    Route::delete('designation-delete/{encryptedId}', [DesignationManagementController::class, 'destroy'])->name('designations.destroy');

    Route::get('employment-types-list', [\App\Http\Controllers\EmploymentTypeManagementController::class, 'index'])->name('employment-types.index');
    Route::get('employment-type-create', [\App\Http\Controllers\EmploymentTypeManagementController::class, 'create'])->name('employment-types.create');
    Route::post('employment-type-store', [\App\Http\Controllers\EmploymentTypeManagementController::class, 'store'])->name('employment-types.store');
    Route::get('employment-type-edit/{encryptedId}', [\App\Http\Controllers\EmploymentTypeManagementController::class, 'edit'])->name('employment-types.edit');
    Route::put('employment-type-update/{encryptedId}', [\App\Http\Controllers\EmploymentTypeManagementController::class, 'update'])->name('employment-types.update');

    Route::get('departments-list', [DepartmentManagementController::class, 'index'])->name('departments.index');
    Route::get('department-create', [DepartmentManagementController::class, 'create'])->name('departments.create');
    Route::post('department-store', [DepartmentManagementController::class, 'store'])->name('departments.store');
    Route::get('department-edit/{encryptedId}', [DepartmentManagementController::class, 'edit'])->name('departments.edit');
    Route::put('department-update/{encryptedId}', [DepartmentManagementController::class, 'update'])->name('departments.update');
    Route::delete('department-delete/{encryptedId}', [DepartmentManagementController::class, 'destroy'])->name('departments.destroy');

    // =============================================
    // SHIFT MANAGEMENT
    // =============================================
    Route::get('shifts-list', '\App\Http\Controllers\Admin\ShiftController@index')->name('admin.shifts.index');
    Route::get('shift-create', '\App\Http\Controllers\Admin\ShiftController@create')->name('admin.shifts.create');
    Route::post('shift-store', '\App\Http\Controllers\Admin\ShiftController@store')->name('admin.shifts.store');
    Route::get('shift-show/{encryptedId}', '\App\Http\Controllers\Admin\ShiftController@show')->name('admin.shifts.show');
    Route::get('shift-edit/{encryptedId}', '\App\Http\Controllers\Admin\ShiftController@edit')->name('admin.shifts.edit');
    Route::put('shift-update/{encryptedId}', '\App\Http\Controllers\Admin\ShiftController@update')->name('admin.shifts.update');
    Route::delete('shift-delete/{encryptedId}', '\App\Http\Controllers\Admin\ShiftController@destroy')->name('admin.shifts.destroy');
    Route::get('shifts/{encryptedId}/assign', '\App\Http\Controllers\Admin\ShiftController@showAssignForm')->name('admin.shifts.assign.show');
    Route::post('shifts/{encryptedId}/assign', '\App\Http\Controllers\Admin\ShiftController@assignShift')->name('admin.shifts.assign');

    // =============================================
    // ASSET MANAGEMENT
    // =============================================
    Route::prefix('assets')->name('assets.')->group(function () {

        // *** ASSETS ***
        Route::get('/index', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/store', [AssetController::class, 'store'])->name('store');
        Route::get('/show/{encryptedId}', [AssetController::class, 'show'])->name('show');
        Route::get('/{encryptedId}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{encryptedId}/update', [AssetController::class, 'update'])->name('update');
        Route::delete('/{encryptedId}/delete', [AssetController::class, 'destroy'])->name('destroy');
        Route::get('/dashboard', [AssetController::class, 'dashboard'])->name('dashboard');
        Route::get('/employees', [AssetController::class, 'employeesWithAssets'])->name('employees');
        Route::get('/own', [AssetController::class, 'ownAssets'])->name('ownAssets');

        // *** ASSET CATEGORIES ***
        Route::get('asset-categories-list', [AssetCategoryController::class, 'index'])->name('categories.index');
        Route::get('asset-category-create', [AssetCategoryController::class, 'create'])->name('categories.create');
        Route::post('asset-category-store', [AssetCategoryController::class, 'store'])->name('categories.store');
        Route::get('asset-category-show/{encryptedId}', [AssetCategoryController::class, 'show'])->name('categories.show');
        Route::get('asset-category-edit/{encryptedId}', [AssetCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('asset-category-update/{encryptedId}', [AssetCategoryController::class, 'update'])->name('categories.update');
        Route::delete('asset-category-delete/{encryptedId}', [AssetCategoryController::class, 'destroy'])->name('categories.destroy');

        // *** ASSET ASSIGNMENTS ***
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [AssetAssignmentController::class, 'index'])->name('index');
            Route::get('/create', [AssetAssignmentController::class, 'create'])->name('create');
            Route::post('/', [AssetAssignmentController::class, 'store'])->name('store');
            Route::get('/show/{encryptedId}', [AssetAssignmentController::class, 'show'])->name('show');
            Route::get('/{encryptedId}/edit', [AssetAssignmentController::class, 'edit'])->name('edit');
            Route::put('/{encryptedId}/update', [AssetAssignmentController::class, 'update'])->name('update');
            Route::delete('/{encryptedId}/delete', [AssetAssignmentController::class, 'destroy'])->name('destroy');
            Route::post('/{encryptedId}/return', [AssetAssignmentController::class, 'returnAsset'])->name('return');
            Route::get('/recent', [AssetAssignmentController::class, 'recentAssignments'])->name('recent');
        });
    });

    // =============================================
    // HOLIDAYS & HOLIDAYS MANAGEMENT
    // =============================================
    Route::prefix('academic-holidays')->name('academic-holidays.')->group(function () {
        Route::get('/', [AcademicHolidayController::class, 'index'])->name('index');
        Route::get('/employee-index', [AcademicHolidayController::class, 'EmployeeIndex'])->name('employee.index');
        Route::get('/create', [AcademicHolidayController::class, 'create'])->name('create');
        Route::post('/', [AcademicHolidayController::class, 'store'])->name('store');
        Route::get('/{encryptedId}/edit', [AcademicHolidayController::class, 'edit'])->name('edit');
        Route::put('/{encryptedId}/update', [AcademicHolidayController::class, 'update'])->name('update');
        Route::delete('/{encryptedId}/delete', [AcademicHolidayController::class, 'destroy'])->name('destroy');
        Route::post('/import', [AcademicHolidayController::class, 'import'])->name('import');
        Route::get('/template', [AcademicHolidayController::class, 'downloadTemplate'])->name('template');
    });

    // =============================================
    // HANDBOOKS & POLICIES
    // =============================================
    Route::get('handbooks-list', [HandbookController::class, 'index'])->name('handbooks.index');
    Route::get('employee-handbooks', [HandbookController::class, 'employeeIndex'])->name('handbooks.employee.index');
    Route::get('handbook-create', [HandbookController::class, 'create'])->name('handbooks.create');
    Route::post('handbook-store', [HandbookController::class, 'store'])->name('handbooks.store');
    Route::get('handbook-show/{handbookId}', [HandbookController::class, 'show'])->name('handbooks.show');
    Route::get('handbook-edit/{handbookId}', [HandbookController::class, 'edit'])->name('handbooks.edit');
    Route::put('handbook-update/{handbookId}', [HandbookController::class, 'update'])->name('handbooks.update');
    Route::delete('handbook-delete/{handbookId}', [HandbookController::class, 'destroy'])->name('handbooks.destroy');
    Route::get('handbooks/{handbookId}/download', [HandbookController::class, 'download'])->name('handbooks.download');
    Route::post('handbooks/{handbookId}/acknowledge', [HandbookController::class, 'acknowledge'])->name('handbooks.acknowledge');

    // =============================================
    // FIELD VISIT MANAGEMENT
    // =============================================
    Route::get('/field-visits/pending', [FieldVisitController::class, 'pendingApprovals'])->name('field-visits.pending');
    Route::get('field-visits-list', [FieldVisitController::class, 'index'])->name('field-visits.index');
    Route::get('field-visit-create', [FieldVisitController::class, 'create'])->name('field-visits.create');
    Route::get('field-visit-show/{field_visit}', [FieldVisitController::class, 'show'])->name('field-visits.show');
    Route::get('field-visit-edit/{field_visit}', [FieldVisitController::class, 'edit'])->name('field-visits.edit');
    Route::put('field-visit-update/{field_visit}', [FieldVisitController::class, 'update'])->name('field-visits.update');
    Route::delete('field-visit-delete/{field_visit}', [FieldVisitController::class, 'destroy'])->name('field-visits.destroy');
    Route::post('/field-visits', [FieldVisitController::class, 'store'])->name('field-visits.store');
    Route::post('/field-visits/{fieldVisit}/approve', [FieldVisitController::class, 'approve'])->name('field-visits.approve');
    Route::post('/field-visits/{fieldVisit}/reject', [FieldVisitController::class, 'reject'])->name('field-visits.reject');
    Route::post('/field-visits/{fieldVisit}/start', [FieldVisitController::class, 'start'])->name('field-visits.start');
    Route::post('/field-visits/{fieldVisit}/complete', [FieldVisitController::class, 'complete'])->name('field-visits.complete');

    // =============================================
    // REIMBURSEMENT MANAGEMENT
    // =============================================
    Route::get('/reimbursements', [ReimbursementController::class, 'index'])->name('reimbursements.index');
    Route::get('/reimbursements/create', [ReimbursementController::class, 'create'])->name('reimbursements.create');
    Route::post('/reimbursements', [ReimbursementController::class, 'store'])->name('reimbursements.store');
    Route::get('/reimbursements/{reimbursement}', [ReimbursementController::class, 'show'])->name('reimbursements.show');
    Route::post('/reimbursements/{reimbursement}/approve', [ReimbursementController::class, 'approve'])->name('reimbursements.approve');
    Route::post('/reimbursements/{reimbursement}/approve/reporter', [ReimbursementController::class, 'approveReporter'])->name('reimbursements.approve.reporter');
    Route::post('/reimbursements/{reimbursement}/reject', [ReimbursementController::class, 'reject'])->name('reimbursements.reject');
    Route::get('/reimbursements/pending', [ReimbursementController::class, 'pending'])->name('reimbursements.pending');

    // =============================================
    // EMPLOYEE MANAGEMENT
    // =============================================
    Route::prefix('employees-management')->name('employees.management.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CompanyAdminController::class, 'employees'])->name('index');
        Route::get('/create', [\App\Http\Controllers\CompanyAdminController::class, 'createEmployee'])->name('create');
        Route::post('/', [\App\Http\Controllers\CompanyAdminController::class, 'storeEmployee'])->name('store');
        Route::get('/{id}/view', [\App\Http\Controllers\CompanyAdminController::class, 'viewEmployee'])->name('view');
        Route::get('/{id}/edit', [\App\Http\Controllers\CompanyAdminController::class, 'editEmployee'])->name('edit');
        Route::put('/{id}/update', [\App\Http\Controllers\CompanyAdminController::class, 'updateEmployee'])->name('update');
        Route::put('/{employee}/role', [\App\Http\Controllers\CompanyAdminController::class, 'updateEmployeeRole'])->name('update-role');
        Route::post('/{id}/toggle-status', [\App\Http\Controllers\CompanyAdminController::class, 'toggleStatus'])->name('toggleStatus');
        Route::get('/next-employee-code', [\App\Http\Controllers\CompanyAdminController::class, 'getNextEmployeeCode'])->name('next-code');
    });

    // Employee Profile
    Route::middleware(['auth'])->prefix('employee')->name('employee.')->group(function () {
        Route::get('profile', [\App\Http\Controllers\Employee\ProfileController::class, 'show'])->name('profile');
        Route::post('profile/update', [\App\Http\Controllers\Employee\ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/update-image', [\App\Http\Controllers\Employee\ProfileController::class, 'updateImage'])->name('profile.update-image');
        Route::get('colleagues', [EmployeeController::class, 'listColleagues'])->name('colleagues');
    });

    // =============================================
    // RESIGNATION & EXIT MANAGEMENT
    // =============================================
    Route::prefix('resignations')->name('resignations.')->group(function () {

        // Employee Resignations
        Route::get('/my-resignations', [ResignationController::class, 'index'])->name('my-resignations.index');
        Route::get('/my-resignations/create', [ResignationController::class, 'create'])->name('my-resignations.create');
        Route::post('/my-resignations', [ResignationController::class, 'store'])->name('my-resignations.store');
        Route::get('/my-resignations/{my_resignation}', [ResignationController::class, 'show'])->name('my-resignations.show');
        Route::get('/my-resignations/{my_resignation}/edit', [ResignationController::class, 'edit'])->name('my-resignations.edit');
        Route::put('/my-resignations/{my_resignation}', [ResignationController::class, 'update'])->name('my-resignations.update');
        Route::post('/my-resignations/{resignation}/withdraw', [ResignationController::class, 'withdraw'])->name('my-resignations.withdraw');

        // Admin Resignation Management
        Route::get('', [App\Http\Controllers\Admin\ResignationController::class, 'index'])->name('index');
        Route::get('/{resignation}', [App\Http\Controllers\Admin\ResignationController::class, 'show'])->name('show');
        Route::post('/{resignation}/approve', [App\Http\Controllers\Admin\ResignationController::class, 'approve'])->name('approve');
        Route::post('/{resignation}/reject', [App\Http\Controllers\Admin\ResignationController::class, 'reject'])->name('reject');

        // Exit Process Management
        Route::post('/{resignation}/complete-exit-interview', [App\Http\Controllers\Admin\ResignationController::class, 'completeExitInterview'])->name('complete-exit-interview');
        Route::post('/{resignation}/complete-handover', [App\Http\Controllers\Admin\ResignationController::class, 'completeHandover'])->name('complete-handover');
        Route::get('/{resignation}/assigned-assets', [App\Http\Controllers\Admin\ResignationController::class, 'getAssignedAssets'])->name('assigned-assets');
        Route::post('/{resignation}/mark-assets-returned', [App\Http\Controllers\Admin\ResignationController::class, 'markAssetsReturned'])->name('mark-assets-returned');
        Route::post('/{resignation}/complete-final-settlement', [App\Http\Controllers\Admin\ResignationController::class, 'completeFinalSettlement'])->name('complete-final-settlement');
    });

    // =============================================
    // COMPANY SETTINGS & MANAGEMENT
    // =============================================
    Route::prefix('company')->name('company.')->group(function () {

        // Company Settings
        Route::get('/settings', [\App\Http\Controllers\CompanyAdminController::class, 'settings'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\CompanyAdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/save-employee-id-prefix', [\App\Http\Controllers\CompanyAdminController::class, 'saveEmployeeIdPrefix'])->name('settings.save-employee-id-prefix');

        // Company Role Management
        Route::get('company-roles-list', [\App\Http\Controllers\CompanyAdmin\RoleController::class, 'index'])->name('roles.index');
        Route::get('company-role-create', [\App\Http\Controllers\CompanyAdmin\RoleController::class, 'create'])->name('roles.create');
        Route::post('company-role-store', [\App\Http\Controllers\CompanyAdmin\RoleController::class, 'store'])->name('roles.store');
        Route::get('company-role-show/{role}', [\App\Http\Controllers\CompanyAdmin\RoleController::class, 'show'])->name('roles.show');
        Route::get('company-role-edit/{role}', [\App\Http\Controllers\CompanyAdmin\RoleController::class, 'edit'])->name('roles.edit');
        Route::put('company-role-update/{role}', [\App\Http\Controllers\CompanyAdmin\RoleController::class, 'update'])->name('roles.update');
        Route::delete('company-role-delete/{role}', [\App\Http\Controllers\CompanyAdmin\RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // =============================================
    // LEADS MANAGEMENT (CRM)
    // =============================================
    Route::middleware(['auth'])->prefix('company-admin')->name('company-admin.')->group(function () {
        Route::get('leads-list', [LeadController::class, 'index'])->name('leads.index');
        Route::get('lead-create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('lead-store', [LeadController::class, 'store'])->name('leads.store');
        Route::get('lead-show/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::get('lead-edit/{lead}', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('lead-update/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('lead-delete/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

        // Module Access Management
        Route::get('/module-access', [\App\Http\Controllers\CompanyAdminController::class, 'moduleAccess'])->name('module-access.index');
        Route::put('/module-access', [\App\Http\Controllers\CompanyAdminController::class, 'updateModuleAccess'])->name('module-access.update');
    });

});

/**
 * =============================================
 * PAYROLL MANAGEMENT ROUTES - CONSOLIDATED SECTION
 * =============================================
 */

// Admin Payroll Management Routes
Route::middleware(['auth'])->group(function () {

    // Main Admin Payroll Routes
    Route::middleware('auth')->group(function () {

         // Employee Payroll Management
Route::get('employee/payroll/', [EmployeePayrollController::class, 'index'])->name('employee.payroll.index'); // List my payslips
Route::get('employee/payroll/{payroll}', [EmployeePayrollController::class, 'show'])->name('employee.payroll.show'); // View a specific payslip
Route::get('employee/payroll/{payroll}/download', [EmployeePayrollController::class, 'downloadPayslip'])->name('employee.payroll.download'); // Download payslip PDF

Route::get('/admin/payroll/index', [AdminPayrollController::class, 'index'])->name('index');
Route::post('/admin/payroll', [AdminPayrollController::class, 'store'])->name('store');
Route::get('/admin/payroll/create', [AdminPayrollController::class, 'create'])->name('create');

        // Payroll Settings - MUST be before {payroll} wildcard route
Route::get('/admin/payroll/settings', [App\Http\Controllers\Admin\PayrollSettingsController::class, 'edit'])->name('settings.edit');
Route::put('/admin/payroll/settings', [App\Http\Controllers\Admin\PayrollSettingsController::class, 'update'])->name('settings.update');
Route::post('/admin/payroll/bulk-approve', [AdminPayrollController::class, 'bulkApprove'])->name('bulkApprove');

       // Employee Payroll Configurations - standalone routes
Route::get('admin/payroll/employee-configurations', [EmployeePayrollConfigController::class, 'index'])->name('admin.payroll.employee-configurations.index');
Route::get('admin/payroll/employee-configurations/{employee}/edit', [EmployeePayrollConfigController::class, 'edit'])->name('admin.payroll.employee-configurations.edit');
Route::put('admin/payroll/employee-configurations/{employee}', [EmployeePayrollConfigController::class, 'update'])->name('admin.payroll.employee-configurations.update');
Route::put('admin/payroll/employee-configurations/{employee}/set-current/{employeeSalary?}', [EmployeePayrollConfigController::class, 'setCurrent'])->name('admin.payroll.employee-configurations.set-current');
Route::post('admin/payroll/employee-configurations/{employee}/create-salary', [EmployeePayrollConfigController::class, 'createSalary'])->name('admin.payroll.employee-configurations.create-salary');


Route::get('admin/employee-payroll-configurations/{employee}/edit', [App\Http\Controllers\Admin\EmployeePayrollConfigController::class, 'edit'])->name('admin.employee-payroll-configurations.edit');
Route::put('admin/employee-payroll-configurations/{employee}', [App\Http\Controllers\Admin\EmployeePayrollConfigController::class, 'update'])->name('admin.employee-payroll-configurations.update');
Route::put('admin/employee-payroll-configurations/{employee}/update-salary', [App\Http\Controllers\Admin\EmployeePayrollConfigController::class, 'updateSalary'])->name('admin.employee-payroll-configurations.update-salary');


       // Beneficiary Badges (Allowances/Deductions) - standalone routes
Route::get('admin/payroll/beneficiary-badges/index', [BeneficiaryBadgeController::class, 'index'])->name('admin.payroll.beneficiary-badges.index');
Route::post('admin/payroll/beneficiary-badges/store', [BeneficiaryBadgeController::class, 'store'])->name('admin.payroll.beneficiary-badges.store');
Route::get('admin/payroll/beneficiary-badges/create', [BeneficiaryBadgeController::class, 'create'])->name('admin.payroll.beneficiary-badges.create');
Route::get('admin/payroll/beneficiary-badges/{beneficiaryBadge}', [BeneficiaryBadgeController::class, 'show'])->name('admin.payroll.beneficiary-badges.show');
Route::get('admin/payroll/beneficiary-badges/{beneficiaryBadge}/edit', [BeneficiaryBadgeController::class, 'edit'])->name('admin.payroll.beneficiary-badges.edit');
Route::put('admin/payroll/beneficiary-badges/{beneficiaryBadge}', [BeneficiaryBadgeController::class, 'update'])->name('admin.payroll.beneficiary-badges.update');
Route::delete('admin/payroll/beneficiary-badges/{beneficiaryBadge}/destroy', [BeneficiaryBadgeController::class, 'destroy'])->name('admin.payroll.beneficiary-badges.destroy');
Route::post('admin/payroll/beneficiary-badges/{beneficiaryBadge}/apply-to-all', [BeneficiaryBadgeController::class, 'applyToAllEmployees'])->name('apply-to-all');
Route::post('admin/payroll/beneficiary-badges/{beneficiaryBadge}/api/apply-to-all', [BeneficiaryBadgeController::class, 'apiApplyToAllEmployees'])->name('api.apply-to-all');


        // Wildcard routes must come after specific routes
Route::get('admin/payroll/{payroll}', [AdminPayrollController::class, 'show'])->name('show');
Route::get('/admin/payroll/{payroll}/edit', [AdminPayrollController::class, 'edit'])->name('edit');
Route::put('/admin/payroll/{payroll}/update', [AdminPayrollController::class, 'update'])->name('update');
Route::patch('/admin/payroll/{payroll}/process', [AdminPayrollController::class, 'processPayroll'])->name('process');
Route::patch('/admin/payroll/{payroll}/mark-as-paid', [AdminPayrollController::class, 'markAsPaid'])->name('markAsPaid');
Route::patch('/admin/payroll/{payroll}/cancel', [AdminPayrollController::class, 'cancel'])->name('cancel');
Route::delete('/admin/payroll/{payroll}/destroy', [AdminPayrollController::class, 'destroy'])->name('destroy');
    });


   // PDF Payslip Routes
        Route::get('employee/salary/payslips', [\App\Http\Controllers\PayslipController::class, 'listPayslips'])->name('employee.salary.payslips');

    });
});
