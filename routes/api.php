<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EmployeeEndPoints\LoginController as APILoginController;
use App\Http\Controllers\API\EmployeeEndPoints\ProfileController;
use App\Http\Controllers\API\EmployeeEndPoints\AttendanceController;
use App\Http\Controllers\API\EmployeeEndPoints\LeaveController;
use App\Http\Controllers\API\EmployeeEndPoints\AnnouncementController;
use App\Http\Controllers\API\EmployeeEndPoints\AcademicHolidayController;
use App\Http\Controllers\API\EmployeeEndPoints\AttendanceRegularizationController;
use App\Http\Controllers\API\EmployeeEndPoints\PayrollController;
use App\Http\Controllers\API\EmployeeEndPoints\ReimbursementController;
use App\Http\Controllers\API\EmployeeEndPoints\TeamController;
use App\Http\Controllers\API\EmployeeEndPoints\FieldVisitController;
use App\Http\Controllers\API\EmployeeEndPoints\ResignationController;
use App\Http\Controllers\API\ContactMessageController;
use App\Http\Controllers\API\DemoRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your application!
|
*/

Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('login', [APILoginController::class, 'login']);
    Route::post('forget-password', [APILoginController::class, 'sendResetLinkEmail'])->middleware('throttle:2,1');

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Route::post('logout', [APILoginController::class, 'logout']);
        // Route::get('user', [APILoginController::class, 'user']);

        // Profile Management
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'getProfile']);
            // Route::post('/update', [ProfileController::class, 'updateProfile']);
            Route::get('/colleagues', [ProfileController::class, 'getColleagues']);
        });

        // Attendance Management
        Route::prefix('attendance')->group(function () {
            Route::get('/history', [AttendanceController::class, 'getAttendanceHistory'])->middleware('check.attendance.access');
            Route::get('/check-in-out', [AttendanceController::class, 'getAttendanceSettingsAndToday'])->middleware('check.attendance.access');
            Route::post('/check-in', [AttendanceController::class, 'checkIn'])->middleware('check.attendance.access');
            Route::post('/validate-location', [AttendanceController::class, 'validateLocation'])->middleware('check.attendance.access');
            Route::post('/check-out', [AttendanceController::class, 'checkOut'])->middleware('check.attendance.access');

            // Regularization
            Route::get('/regularization', [AttendanceRegularizationController::class, 'getRegularizationRequests']);
            Route::post('/regularization', [AttendanceRegularizationController::class, 'CreateRegularizationRequest']);
        });

        // Leave Management
        Route::prefix('leave')->group(function () {
            Route::get('/types', [LeaveController::class, 'getLeaveTypes']);
            Route::get('/balance', [LeaveController::class, 'getLeaveBalance']);
            Route::get('/requests', [LeaveController::class, 'getLeaveRequests']);
            Route::post('/apply', [LeaveController::class, 'applyLeave']);
            Route::post('/{id}/cancel', [LeaveController::class, 'cancelLeave']);
        });

        // Announcements
        Route::prefix('announcements')->group(function () {
            Route::get('/', [AnnouncementController::class, 'getAnnouncements']);
            Route::get('/{id}', [AnnouncementController::class, 'getAnnouncement']);
        });

        // Holiday Routes
        Route::get('/holidays', [AcademicHolidayController::class, 'getHolidays']);
        Route::get('/holidays/calendar', [AcademicHolidayController::class, 'getCalendar']);
        Route::get('/holidays/{id}', [AcademicHolidayController::class, 'getHoliday']);
        Route::get('/holidays/employee/index', [AcademicHolidayController::class, 'getEmployeeHolidays']);

        // Salary Management
        Route::prefix('salary')->group(function () {
            Route::get('/current', [PayrollController::class, 'getCurrentSalary']);
            Route::get('/payroll-records', [PayrollController::class, 'getPayrollRecords']);
            // Route::get('/payroll-records/{id}', [PayrollController::class, 'getPayrollRecord']);
            Route::get('/payslip/{id}', [PayrollController::class, 'downloadPayslip']);
        });

        // Reimbursement Management
        Route::prefix('reimbursements')->group(function () {
            Route::get('/', [ReimbursementController::class, 'getReimbursements']);
            Route::get('/{id}', [ReimbursementController::class, 'getReimbursement']);
            Route::post('/', [ReimbursementController::class, 'createReimbursement']);
            Route::post('/{id}', [ReimbursementController::class, 'updateReimbursement']);
            Route::delete('/{id}', [ReimbursementController::class, 'cancelReimbursement']);
        });

        // Field Visit Management
        Route::prefix('field-visits')->group(function () {
            Route::get('/', [FieldVisitController::class, 'getFieldVisits']);
            Route::get('/{id}', [FieldVisitController::class, 'getFieldVisit']);
            Route::get('/upcoming/list ', [FieldVisitController::class, 'getUpcomingFieldVisits']);
            Route::get('/stats/summary', [FieldVisitController::class, 'getFieldVisitStats']);
            Route::post('/', [FieldVisitController::class, 'createFieldVisit']);
            Route::post('/{id}/approve', [FieldVisitController::class, 'approveFieldVisit']);
            Route::post('/{id}/reject', [FieldVisitController::class, 'rejectFieldVisit']);
            Route::get('/pending', [FieldVisitController::class, 'getPendingApprovals']);
        });

        // Resignation Management
        Route::prefix('resignations')->group(function () {
            Route::get('/', [ResignationController::class, 'getResignations']);
            Route::get('/{id}', [ResignationController::class, 'getResignation']);
            Route::post('/create', [ResignationController::class, 'createResignation']);
            Route::post('/{id}', [ResignationController::class, 'updateResignation']);
            Route::post('/{id}/withdraw', [ResignationController::class, 'withdrawResignation']);
            Route::get('/types/list', [ResignationController::class, 'getResignationTypes']);
        });

    });
});

Route::post('/contact-messages', [ContactMessageController::class, 'store']);
Route::post('/demo-requests', [DemoRequestController::class, 'store']);
