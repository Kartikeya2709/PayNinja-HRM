<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllSectionsSlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds all 9 out of 17 major sections with ALL their routes from web.php (excluding SuperAdmin section)
     * Total: 9 parent sections + 143 child routes
     */
    public function run()
    {
        $sections = [
            // 1. DASHBOARD & PROFILE
            [
                'name' => 'Dashboard & Profile',
                'slug' => 'dashboard-profile',
                'icon' => 'fas fa-home',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Home', 'slug' => '/home', 'icon' => 'fas fa-tachometer-alt', 'is_visible' => 1, 'sort_order' => 1],
                    // ['name' => 'Blank Page', 'slug' => '/blank-page', 'icon' => 'fas fa-file', 'is_visible' => 0, 'sort_order' => 2],
                    ['name' => 'Dashboard Switch', 'slug' => '/dashboard/switch', 'icon' => 'fas fa-exchange-alt', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Profile Edit', 'slug' => '/profile/edit', 'icon' => 'fas fa-user-edit', 'is_visible' => 1, 'sort_order' => 4],
                    ['name' => 'Profile Update', 'slug' => '/profile/update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Change Password', 'slug' => '/profile/change-password', 'icon' => 'fas fa-key', 'is_visible' => 1, 'sort_order' => 6],
                    ['name' => 'Update Password', 'slug' => '/profile/password', 'icon' => 'fas fa-lock', 'is_visible' => 0, 'sort_order' => 7],
                ]
            ],

            // 2. ANNOUNCEMENTS
            [
                'name' => 'Announcements',
                'slug' => 'announcements',
                'icon' => 'fas fa-bullhorn',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Announcements List', 'slug' => 'announcements-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Announcement', 'slug' => 'announcement-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Store Announcement', 'slug' => 'announcement-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Show Announcement', 'slug' => 'announcement-show/{announcement}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Announcement', 'slug' => 'announcement-edit/{announcement}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Announcement', 'slug' => 'announcement-update/{announcement}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Delete Announcement', 'slug' => 'announcement-delete/{announcement}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 7],
                ]
            ],

            // 3. ATTENDANCE MANAGEMENT
            [
                'name' => 'Attendance Management',
                'slug' => 'attendance-management',
                'icon' => 'fas fa-calendar-check',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 3,
                'children' => [
                    // Regularization
                    // ['name' => 'Regularization Requests', 'slug' => 'attendance-management/regularization-requests-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Regularization', 'slug' => 'attendance-management/regularization-request-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 2],
                    ['name' => 'Store Regularization', 'slug' => 'attendance-management/regularization-request-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Show Regularization', 'slug' => 'attendance-management/regularization-request-show/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Regularization', 'slug' => 'attendance-management/regularization-request-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Regularization', 'slug' => 'attendance-management/regularization-request-update/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Delete Regularization', 'slug' => 'attendance-management/regularization-request-delete/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'My Regularization Requests', 'slug' => 'attendance-management/regularization-requests/my', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 8],
                    // ['name' => 'Show My Regularization Requests', 'slug' => 'attendance-management/regularization-requests/my/show', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 9],
                    ['name' => 'Delete My Regularization Requests', 'slug' => 'attendance-management/regularization-requests/my/delete', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 10],
                    ['name' => 'Company Regularization Requests', 'slug' => 'attendance-management/regularization-requests/company', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 11],
                    // ['name' => 'Approve Regularization', 'slug' => 'attendance-management/regularization-requests/{encryptedId}/approve', 'icon' => 'fas fa-check', 'is_visible' => 0, 'sort_order' => 12],
                    ['name' => 'Bulk Approve/Reject Regularization', 'slug' => 'attendance-management/regularization-requests/bulk-update', 'icon' => 'fas fa-check', 'is_visible' => 0, 'sort_order' => 13],

                    // Admin Attendance
                    ['name' => 'Master Attendance', 'slug' => 'attendance-management/attendance', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 10],
                    ['name' => 'Attendance Summary', 'slug' => 'attendance-management/attendance/summary', 'icon' => 'fas fa-chart-bar', 'is_visible' => 1, 'sort_order' => 11],
                    ['name' => 'Store Master Attendance', 'slug' => 'attendance-management/attendance/store', 'icon' => 'fas fa-list', 'is_visible' => 0, 'sort_order' => 12],
                    ['name' => 'Edit Master Attendance', 'slug' => 'attendance-management/attendance/{encryptedId}/edit', 'icon' => 'fas fa-list', 'is_visible' => 0, 'sort_order' => 13],
                    ['name' => 'Update Master Attendance', 'slug' => 'attendance-management/attendance/{encryptedId}/update', 'icon' => 'fas fa-list', 'is_visible' => 0, 'sort_order' => 14],
                    ['name' => 'Delete Master Attendance', 'slug' => 'attendance-management/attendance/{encryptedId}/delete', 'icon' => 'fas fa-list', 'is_visible' => 0, 'sort_order' => 15],
                    ['name' => 'Import Attendance', 'slug' => 'attendance-management/attendance/import', 'icon' => 'fas fa-upload', 'is_visible' => 0, 'sort_order' => 16],
                    ['name' => 'Import-Results Attendance', 'slug' => 'attendance-management/attendance/import-results', 'icon' => 'fas fa-upload', 'is_visible' => 0, 'sort_order' => 17],
                    ['name' => 'Export Attendance', 'slug' => 'attendance-management/attendance/export', 'icon' => 'fas fa-download', 'is_visible' => 0, 'sort_order' => 18],
                    ['name' => 'Template Attendance', 'slug' => 'attendance-management/attendance/template', 'icon' => 'fas fa-download', 'is_visible' => 0, 'sort_order' => 19],
                    ['name' => 'Attendance Settings', 'slug' => 'attendance-management/attendance/settings', 'icon' => 'fas fa-cog', 'is_visible' => 1, 'sort_order' => 20],
                    ['name' => 'Attendance-View Settings', 'slug' => 'attendance-management/attendance/settings/view', 'icon' => 'fas fa-cog', 'is_visible' => 1, 'sort_order' => 21],
                    ['name' => 'Attendance-Put Settings', 'slug' => 'attendance-management/attendance/settings/put', 'icon' => 'fas fa-cog', 'is_visible' => 0, 'sort_order' => 22],
                    ['name' => 'Attendance-Api office Timings Settings', 'slug' => 'attendance-management/attendance/api/office-timings', 'icon' => 'fas fa-cog', 'is_visible' => 0, 'sort_order' => 23],

                    // Employee Attendance
                    ['name' => 'Attendance Dashboard', 'slug' => 'attendance-management/attendance/dashboard', 'icon' => 'fas fa-chart-line', 'is_visible' => 1, 'sort_order' => 24],
                    ['name' => 'Show Attendance', 'slug' => 'attendance-management/attendance/{encryptedId}/show', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 25],
                    ['name' => 'Check In/Out', 'slug' => 'attendance-management/attendance/check-in-out', 'icon' => 'fas fa-clock', 'is_visible' => 1, 'sort_order' => 26],
                    ['name' => 'My Attendance', 'slug' => 'attendance-management/attendance/my-attendance', 'icon' => 'fas fa-user-check', 'is_visible' => 1, 'sort_order' => 27],
                    ['name' => 'Export Attendance', 'slug' => 'attendance-management/attendance/my-attendance/export', 'icon' => 'fas fa-user-check', 'is_visible' => 0, 'sort_order' => 28],
                    ['name' => 'Export-PDF Attendance', 'slug' => 'attendance-management/attendance/my-attendance/export-pdf', 'icon' => 'fas fa-user-check', 'is_visible' => 0, 'sort_order' => 29],
                    // ['name' => 'Check-Location Attendance', 'slug' => 'attendance-management/attendance/my-attendance/check-location', 'icon' => 'fas fa-user-check', 'is_visible' => 1, 'sort_order' => 30],
                    ['name' => 'Geolocation-settings Attendance', 'slug' => 'attendance-management/geolocation-settings', 'icon' => 'fas fa-user-check', 'is_visible' => 0, 'sort_order' => 31],
                    ['name' => 'Check In', 'slug' => 'attendance-management/attendance/check-in', 'icon' => 'fas fa-sign-in-alt', 'is_visible' => 0, 'sort_order' => 32],
                    ['name' => 'Check Out', 'slug' => 'attendance-management/attendance/check-out', 'icon' => 'fas fa-sign-out-alt', 'is_visible' => 0, 'sort_order' => 33],
                ]
            ],

            // 4. LEAVE MANAGEMENT
            [
                'name' => 'Leave Management',
                'slug' => 'leave-management',
                'icon' => 'fas fa-umbrella-beach',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 4,
                'children' => [
                    // Leave Types
                    ['name' => 'Leave Types', 'slug' => 'leaves/leave-types-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Leave Type', 'slug' => 'leaves/leave-type-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 2],
                    ['name' => 'Store Leave Type', 'slug' => 'leaves/leave-type-store', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'show Leave Type', 'slug' => 'leaves/leave-type-show/{leave_type}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Leave Type', 'slug' => 'leaves/leave-type-edit/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Leave Type', 'slug' => 'leaves/leave-type-update/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Delete Leave Type', 'slug' => 'leaves/leave-type-delete/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 7],

                    // Leave Balances
                    ['name' => 'Leave Balances', 'slug' => 'leaves/leave-balances-list', 'icon' => 'fas fa-wallet', 'is_visible' => 1, 'sort_order' => 8],
                    ['name' => 'Create Leave Balance', 'slug' => 'leaves/leave-balance-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 9],
                    ['name' => 'Store Leave Balance', 'slug' => 'leaves/leave-balance-store', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 10],
                    ['name' => 'Edit Leave Balance', 'slug' => 'leaves/leave-balance-edit/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 11],
                    ['name' => 'Update Leave Balance', 'slug' => 'leaves/leave-balance-update/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 12],
                    ['name' => 'Bulk Allocate', 'slug' => 'leaves/leave-balances/bulk-allocate', 'icon' => 'fas fa-check-double', 'is_visible' => 0, 'sort_order' => 13],
                    ['name' => 'Reset Balances', 'slug' => 'leaves/leave-balances/reset', 'icon' => 'fas fa-redo', 'is_visible' => 0, 'sort_order' => 14],
                    ['name' => 'Export Leave Balances', 'slug' => 'leaves/leave-balances/export', 'icon' => 'fas fa-redo', 'is_visible' => 0, 'sort_order' => 15],

                    // Admin Leave Requests
                    ['name' => 'Leave Requests', 'slug' => 'leaves/leave-requests', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 16],
                    // ['name' => 'Leave Calendar', 'slug' => 'leaves/leave-requests/calendar', 'icon' => 'fas fa-calendar', 'is_visible' => 1, 'sort_order' => 17],
                    ['name' => 'Create Leave Request', 'slug' => 'leaves/leave-requests/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 18],
                    ['name' => 'Store Leave Request', 'slug' => 'leaves/leave-requests/store', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 19],
                    ['name' => 'Show Leave Request', 'slug' => 'leaves/leave-requests/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 20],
                    ['name' => 'Approve Leave', 'slug' => 'leaves/leave-requests/{encryptedId}/approve', 'icon' => 'fas fa-check', 'is_visible' => 0, 'sort_order' => 21],
                    ['name' => 'Reject Leave', 'slug' => 'leaves/leave-requests/{encryptedId}/reject', 'icon' => 'fas fa-times', 'is_visible' => 0, 'sort_order' => 22],

                    // Employee Leave Requests
                    ['name' => 'My Leave Requests', 'slug' => 'leaves/my-leaves/my-leave-requests-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 23],
                    ['name' => 'Create Employee Leave Request', 'slug' => 'leaves/my-leaves/my-leave-request-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 24],
                    ['name' => 'Store Employee Leave Request', 'slug' => 'leaves/my-leaves/my-leave-request-store', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 25],
                    ['name' => 'Show Employee Leave Request', 'slug' => 'leaves/my-leaves/my-leave-request-show/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 26],
                    ['name' => 'Edit Employee Leave Request', 'slug' => 'leaves/my-leaves/my-leave-request-edit/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 27],
                    ['name' => 'Update Employee Leave Request', 'slug' => 'leaves/my-leaves/my-leave-request-update/{encryptedId}', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 28],
                    ['name' => 'Cancel Employee Leave Request', 'slug' => 'leaves/my-leaves/leave-requests/{encryptedId}/cancel', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 29],
                    // ['name' => 'Export Employee Leave Request', 'slug' => 'leaves/my-leavesleave-requests/export', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 30],
                    // ['name' => 'History Employee Leave Request', 'slug' => 'leaves/my-leaves/leave-balances/history', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 31],
                    // ['name' => 'My Leave Balances', 'slug' => 'leaves/my-leaves/leave-balances', 'icon' => 'fas fa-wallet', 'is_visible' => 1, 'sort_order' => 32],
                ]
            ],

            // 5. DESIGNATION & ORGANIZATIONAL
            [
                'name' => 'Designation & Organizational',
                'slug' => 'designation-org',
                'icon' => 'fas fa-sitemap',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Designations', 'slug' => 'designations-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Designation', 'slug' => 'designation-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 2],
                    ['name' => 'Store Designation', 'slug' => 'designation-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Edit Designation', 'slug' => 'designation-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Update Designation', 'slug' => 'designation-update/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Delete Designation', 'slug' => 'designation-delete/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 6],
                    // -----------------------------
                    // EMPLOYMENT TYPES
                    // -----------------------------
                    ['name' => 'Employment Types', 'slug' => 'employment-types-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 7],
                    ['name' => 'Create Employment Type', 'slug' => 'employment-type-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Store Employment Type', 'slug' => 'employment-type-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 9],
                    ['name' => 'Edit Employment Type', 'slug' => 'employment-type-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 10],
                    ['name' => 'Update Employment Type', 'slug' => 'employment-type-update/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 11],
                    // -----------------------------
                    // DEPARTMENTS
                    // -----------------------------
                    ['name' => 'Departments', 'slug' => 'departments-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 12],
                    ['name' => 'Create Department', 'slug' => 'department-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 13],
                    ['name' => 'Store Department', 'slug' => 'department-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 14],
                    ['name' => 'Edit Department', 'slug' => 'department-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 15],
                    ['name' => 'Update Department', 'slug' => 'department-update/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 16],
                    ['name' => 'Delete Department', 'slug' => 'department-delete/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 17],
                ]
            ],

            // 6. SHIFT MANAGEMENT
            [
                'name' => 'Shift Management',
                'slug' => 'shift-management',
                'icon' => 'fas fa-business-time',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 6,
                'children' => [
                    ['name' => 'Shifts', 'slug' => 'shifts-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Shift', 'slug' => 'shift-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 2],
                    ['name' => 'Store Shift', 'slug' => 'shift-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Show Shift', 'slug' => 'shift-show/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Shift', 'slug' => 'shift-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Shift', 'slug' => 'shift-update/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Delete Shift', 'slug' => 'shift-delete/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'Assign Shift', 'slug' => 'shifts/{encryptedId}/assign', 'icon' => 'fas fa-user-plus', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Assign Shift Store', 'slug' => 'shifts/{encryptedId}/assign', 'icon' => 'fas fa-user-check', 'is_visible' => 0, 'sort_order' => 9],
                ]
            ],

            // 7. ASSET MANAGEMENT
            [
                'name' => 'Asset Management',
                'slug' => 'asset-management',
                'icon' => 'fas fa-box',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 7,
                'children' => [

                    // -----------------------------
                    // ASSETS
                    // -----------------------------
                    ['name' => 'Assets', 'slug' => 'assets/index', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 18],
                    ['name' => 'Create Asset', 'slug' => 'assets/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 19],
                    ['name' => 'Store Asset', 'slug' => 'assets/store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 20],
                    ['name' => 'Show Asset', 'slug' => 'assets/show/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 21],
                    ['name' => 'Edit Asset', 'slug' => 'assets/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 22],
                    ['name' => 'Update Asset', 'slug' => 'assets/{encryptedId}/update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 23],
                    ['name' => 'Delete Asset', 'slug' => 'assets/{encryptedId}/delete', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 24],
                    ['name' => 'Asset Dashboard', 'slug' => 'assets/dashboard', 'icon' => 'fas fa-tachometer-alt', 'is_visible' => 1, 'sort_order' => 25],
                    ['name' => 'Asset Employees', 'slug' => 'assets/employees', 'icon' => 'fas fa-users', 'is_visible' => 0, 'sort_order' => 26],
                    ['name' => 'Own Assets', 'slug' => 'assets/own', 'icon' => 'fas fa-user', 'is_visible' => 0, 'sort_order' => 27],
                    // -----------------------------
                    // ASSET CATEGORIES
                    // -----------------------------
                    ['name' => 'Asset Categories', 'slug' => 'assets/asset-categories-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 28],
                    ['name' => 'Create Asset Category', 'slug' => 'assets/asset-category-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 29],
                    ['name' => 'Store Asset Category', 'slug' => 'assets/asset-category-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 30],
                    ['name' => 'Show Asset Category', 'slug' => 'assets/asset-category-show/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 31],
                    ['name' => 'Edit Asset Category', 'slug' => 'assets/asset-category-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 32],
                    ['name' => 'Update Asset Category', 'slug' => 'assets/asset-category-update/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 33],
                    ['name' => 'Delete Asset Category', 'slug' => 'assets/asset-category-delete/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 34],
                    // -----------------------------
                    // ASSET ASSIGNMENTS
                    // -----------------------------
                    ['name' => 'Asset Assignments', 'slug' => 'assets/assignments', 'icon' => 'fas fa-exchange-alt', 'is_visible' => 1, 'sort_order' => 35],
                    ['name' => 'Create Asset Assignment', 'slug' => 'assets/assignments/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 36],
                    ['name' => 'Store Asset Assignment', 'slug' => 'assets/assignments/store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 37],
                    ['name' => 'Show Asset Assignment', 'slug' => 'assets/assignments/show/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 38],
                    ['name' => 'Edit Asset Assignment', 'slug' => 'assets/assignments/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 39],
                    ['name' => 'Update Asset Assignment', 'slug' => 'assets/assignments/{encryptedId}/update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 40],
                    ['name' => 'Delete Asset Assignment', 'slug' => 'assets/assignments/{encryptedId}/delete', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 41],
                    ['name' => 'Return Asset Assignment', 'slug' => 'assets/assignments/{encryptedId}/return', 'icon' => 'fas fa-undo', 'is_visible' => 0, 'sort_order' => 42],
                    ['name' => 'Recent Asset Assignments', 'slug' => 'assets/assignments/recent', 'icon' => 'fas fa-clock', 'is_visible' => 0, 'sort_order' => 43],
                ]
            ],

            // 8. HOLIDAYS & HOLIDAYS MANAGEMENT
            [
                'name' => 'Holidays',
                'slug' => 'holidays',
                'icon' => 'fas fa-calendar-times',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 8,
                'children' => [
                    ['name' => 'Academic Holidays', 'slug' => 'academic-holidays', 'icon' => 'fas fa-calendar', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Employee Holidays', 'slug' => 'academic-holidays/employee-index', 'icon' => 'fas fa-calendar-alt', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Create Holiday', 'slug' => 'academic-holidays/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Store Holiday', 'slug' => 'academic-holidays/store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Holiday', 'slug' => 'academic-holidays/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Holiday', 'slug' => 'academic-holidays/{encryptedId}/update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Delete Holiday', 'slug' => 'academic-holidays/{encryptedId}/delete', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'Import Holidays', 'slug' => 'academic-holidays/import', 'icon' => 'fas fa-file-import', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Download Template', 'slug' => 'academic-holidays/template', 'icon' => 'fas fa-download', 'is_visible' => 0, 'sort_order' => 9],
                ]
            ],

            // 9. HANDBOOKS & POLICIES
            [
                'name' => 'Handbooks & Policies',
                'slug' => 'handbooks',
                'icon' => 'fas fa-book',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 9,
                'children' => [
                    ['name' => 'Employee Handbooks', 'slug' => 'employee-handbooks', 'icon' => 'fas fa-book-open', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Admin Handbooks', 'slug' => 'handbooks-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Create Handbook', 'slug' => 'handbook-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Store Handbook', 'slug' => 'handbook-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Show Handbook', 'slug' => 'handbook-show/{handbookId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Handbook', 'slug' => 'handbook-edit/{handbookId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Handbook', 'slug' => 'handbook-update/{handbookId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Delete Handbook', 'slug' => 'handbook-delete/{handbookId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'Download Handbook', 'slug' => 'handbooks/{handbookId}/download', 'icon' => 'fas fa-download', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Acknowledge Handbook', 'slug' => 'handbooks/{handbookId}/acknowledge', 'icon' => 'fas fa-check-circle', 'is_visible' => 0, 'sort_order' => 9],
                ]
            ],

            // 10. FIELD VISIT MANAGEMENT
            [
                'name' => 'Field Visit Management',
                'slug' => 'field-visits-management',
                'icon' => 'fas fa-map-marker-alt',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 10,
                'children' => [
                    ['name' => 'Field Visits', 'slug' => 'field-visits-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'My Field Visits', 'slug' => 'field-visits-my-list', 'icon' => 'fas fa-user-check', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Field Visit', 'slug' => 'field-visit-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Store Field Visit', 'slug' => 'field-visits', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Pending Approvals Own', 'slug' => 'field-visits/pending-own', 'icon' => 'fas fa-clock', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Pending Approvals All', 'slug' => 'field-visits/pending-all', 'icon' => 'fas fa-clock', 'is_visible' => 1, 'sort_order' => 3],
                    ['name' => 'Show Any Field Visit', 'slug' => 'field-visit-show-any/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Show Field Visit', 'slug' => 'field-visit-show/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Field Visit', 'slug' => 'field-visit-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Edit Any Field Visit', 'slug' => 'field-visit-edit-any/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Approve Any Field Visit', 'slug' => 'field-visits/{encryptedId}/approve-any', 'icon' => 'fas fa-check-double', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Approve Field Visit', 'slug' => 'field-visits/{encryptedId}/approve', 'icon' => 'fas fa-check', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Reject Field Visit', 'slug' => 'field-visits/{encryptedId}/reject', 'icon' => 'fas fa-times', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'Reject Any Field Visit', 'slug' => 'field-visits/{encryptedId}/reject-any', 'icon' => 'fas fa-times-circle', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'Update Any Field Visit', 'slug' => 'field-visit-update-any/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Update Field Visit', 'slug' => 'field-visit-update/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Delete Field Visit', 'slug' => 'field-visit-delete/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 9],
                    ['name' => 'Delete Any Field Visit', 'slug' => 'field-visit-delete-any/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 10],
                    ['name' => 'Start Field Visit', 'slug' => 'field-visits/{encryptedId}/start', 'icon' => 'fas fa-play', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'Complete Field Visit', 'slug' => 'field-visits/{encryptedId}/complete', 'icon' => 'fas fa-check-circle', 'is_visible' => 0, 'sort_order' => 8],
                ]
            ],

            // 11. REIMBURSEMENT MANAGEMENT
            [
                'name' => 'Reimbursement Management',
                'slug' => 'reimbursement',
                'icon' => 'fas fa-hand-holding-usd',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 11,
                'children' => [
                    ['name' => 'Reimbursements List All', 'slug' => 'reimbursements/index', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'My reimbursements', 'slug' => 'reimbursements/my-reimbursements', 'icon' => 'fas fa-list-alt', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Reimbursement', 'slug' => 'reimbursements/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Store Reimbursement', 'slug' => 'reimbursements', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Pending Own Reimbursements', 'slug' => 'reimbursements/pending-own', 'icon' => 'fas fa-clock', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Pending All Reimbursements', 'slug' => 'reimbursements/pending-all', 'icon' => 'fas fa-clock', 'is_visible' => 1, 'sort_order' => 3],
                    ['name' => 'Show Reimbursement', 'slug' => 'reimbursements/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Show Any Reimbursement', 'slug' => 'reimbursements/show-any/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Reimbursement', 'slug' => 'reimbursements/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Edit Any Reimbursement', 'slug' => 'reimbursements/edit-any/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Reimbursement', 'slug' => 'reimbursements/{encryptedId}/update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Update Any Reimbursement', 'slug' => 'reimbursements/update-any/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Approve Any Reimbursement', 'slug' => 'reimbursements/{encryptedId}/approve-any', 'icon' => 'fas fa-check-double', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Approve Reimbursement', 'slug' => 'reimbursements/{encryptedId}/approve', 'icon' => 'fas fa-check', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Approve Reimbursement by Reporter', 'slug' => 'reimbursements/{encryptedId}/approve/reporter', 'icon' => 'fas fa-check-double', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Reject Reimbursement', 'slug' => 'reimbursements/{encryptedId}/reject', 'icon' => 'fas fa-times', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Reject Any Reimbursement', 'slug' => 'reimbursements/{encryptedId}/reject-any', 'icon' => 'fas fa-times-circle', 'is_visible' => 0, 'sort_order' => 6],
                ]
            ],

            // 12. EMPLOYEE MANAGEMENT
            [
                'name' => 'Employee Management',
                'slug' => 'employee-management',
                'icon' => 'fas fa-users',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 12,
                'children' => [
                    ['name' => 'Employees', 'slug' => 'employees-management/index', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Employee', 'slug' => 'employees-management/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Store Employee', 'slug' => 'employees-management/', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'View Employee', 'slug' => 'employees-management/{encryptedId}/view', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Employee', 'slug' => 'employees-management/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Employee', 'slug' => 'employees-management/{encryptedId}/update', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Update Employee Role', 'slug' => 'employees-management/{encryptedId}/role', 'icon' => 'fas fa-user-tag', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'Toggle Status', 'slug' => 'employees-management/{encryptedId}/toggle-status', 'icon' => 'fas fa-toggle-on', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Next Employee Code', 'slug' => 'employees-management/next-employee-code', 'icon' => 'fas fa-hashtag', 'is_visible' => 0, 'sort_order' => 9],
                ]
            ],

            // 13. EMPLOYEE PROFILE
            [
                'name' => 'Employee Profile',
                'slug' => 'employee-profile',
                'icon' => 'fas fa-user-circle',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 13,
                'children' => [
                    ['name' => 'My Profile', 'slug' => 'employee/profile', 'icon' => 'fas fa-user', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Update Profile', 'slug' => 'employee/profile/update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 2],
                    ['name' => 'Update Profile Image', 'slug' => 'employee/profile/update-image', 'icon' => 'fas fa-camera', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Colleagues', 'slug' => 'employee/colleagues', 'icon' => 'fas fa-users', 'is_visible' => 1, 'sort_order' => 4],
                ]
            ],

            // 14. RESIGNATION & EXIT MANAGEMENT
            [
                'name' => 'Resignation & Exit',
                'slug' => 'resignation-exit',
                'icon' => 'fas fa-sign-out-alt',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 14,
                'children' => [
                    // Employee Resignations
                    ['name' => 'My Resignations', 'slug' => 'resignations/my-resignations', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Submit Resignation', 'slug' => 'resignations/my-resignations/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Store Resignation', 'slug' => 'resignations/my-resignations', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Show Resignation', 'slug' => 'resignations/my-resignations/{encryptedId}/show', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Edit Resignation', 'slug' => 'resignations/my-resignations/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Update Resignation', 'slug' => 'resignations/my-resignations/{encryptedId}', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Withdraw Resignation', 'slug' => 'resignations/my-resignations/{encryptedId}/withdraw', 'icon' => 'fas fa-undo', 'is_visible' => 0, 'sort_order' => 7],
                    // Admin Resignations
                    ['name' => 'All Resignations', 'slug' => 'resignations', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 8],
                    ['name' => 'Show All Resignations', 'slug' => 'resignations/{resignation}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 9],
                    ['name' => 'Approve Resignation', 'slug' => 'resignations/{resignation}/approve', 'icon' => 'fas fa-check', 'is_visible' => 0, 'sort_order' => 10],
                    ['name' => 'Reject Resignation', 'slug' => 'resignations/{resignation}/reject', 'icon' => 'fas fa-times', 'is_visible' => 0, 'sort_order' => 11],
                    ['name' => 'Exit Interview', 'slug' => 'resignations/{resignation}/complete-exit-interview', 'icon' => 'fas fa-comments', 'is_visible' => 0, 'sort_order' => 12],
                    ['name' => 'Handover', 'slug' => 'resignations/{resignation}/complete-handover', 'icon' => 'fas fa-exchange-alt', 'is_visible' => 0, 'sort_order' => 13],
                    ['name' => 'Assets Return', 'slug' => 'resignations/{resignation}/assigned-assets', 'icon' => 'fas fa-boxes', 'is_visible' => 0, 'sort_order' => 14],
                    ['name' => 'Mark Assets Returned', 'slug' => 'resignations/{resignation}/mark-assets-returned', 'icon' => 'fas fa-check-circle', 'is_visible' => 0, 'sort_order' => 15],
                    ['name' => 'Final Settlement', 'slug' => 'resignations/{resignation}/complete-final-settlement', 'icon' => 'fas fa-file-invoice-dollar', 'is_visible' => 0, 'sort_order' => 16],
                ]
            ],

            // 15. COMPANY SETTINGS & MANAGEMENT
            [
                'name' => 'Company Settings',
                'slug' => 'company-settings',
                'icon' => 'fas fa-cog',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 15,
                'children' => [
                    ['name' => 'Company Settings', 'slug' => 'company/settings', 'icon' => 'fas fa-cog', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Update Company Settings', 'slug' => 'company/settings/update', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 2],
                    ['name' => 'Save Employee ID Prefix', 'slug' => 'company/settings/save-employee-id-prefix', 'icon' => 'fas fa-id-card', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Company Roles', 'slug' => 'company/company-roles-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 4],
                    ['name' => 'Create Role', 'slug' => 'company/company-role-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 5],
                    ['name' => 'Store Role', 'slug' => 'company/company-role-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 6],
                    ['name' => 'Show Role', 'slug' => 'company/company-role-show/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 7],
                    ['name' => 'Edit Role', 'slug' => 'company/company-role-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Update Role', 'slug' => 'company/company-role-update/{encryptedId}', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 9],
                    ['name' => 'Delete Role', 'slug' => 'company/company-role-delete/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 10],
                ]
            ],

            // 16. LEADS MANAGEMENT (CRM)
            [
                'name' => 'Leads Management',
                'slug' => 'leads-crm',
                'icon' => 'fas fa-user-tie',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 16,
                'children' => [
                    ['name' => 'Leads', 'slug' => 'company-admin/leads-list', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Create Lead', 'slug' => 'company-admin/lead-create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 2],
                    ['name' => 'Store Lead', 'slug' => 'company-admin/lead-store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Show Lead', 'slug' => 'company-admin/lead-show/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 3],
                    ['name' => 'Edit Lead', 'slug' => 'company-admin/lead-edit/{encryptedId}', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Update Lead', 'slug' => 'company-admin/lead-update/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 5],
                    ['name' => 'Delete Lead', 'slug' => 'company-admin/lead-delete/{encryptedId}', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 6],

                    //Module Access
                    ['name' => 'Module Access', 'slug' => 'company-admin/module-access', 'icon' => 'fas fa-key', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'Update Module Access', 'slug' => 'company-admin/module-access-update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 2],
                ]
            ],

            // 17. PAYROLL MANAGEMENT
            [
                'name' => 'Payroll Management',
                'slug' => 'payroll',
                'icon' => 'fas fa-money-bill-wave',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 17,
                'children' => [

                    // Employee Payroll
                    ['name' => 'My Payslips', 'slug' => 'employee/payroll/', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 1],
                    ['name' => 'View Payslip', 'slug' => 'employee/payroll/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 2],
                    ['name' => 'Download Payslip', 'slug' => 'employee/payroll/{encryptedId}/download', 'icon' => 'fas fa-download', 'is_visible' => 0, 'sort_order' => 3],

                    // Admin Payroll - Index and Create
                    ['name' => 'Store Payroll Data', 'slug' => 'admin/payroll', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 4],
                    ['name' => 'Payroll Index', 'slug' => 'admin/payroll/index', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 5],
                    ['name' => 'Create Payroll', 'slug' => 'admin/payroll/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 6],

                    // Payroll Settings - MUST be before {payroll} wildcard route
                    ['name' => 'Payroll Settings', 'slug' => 'admin/payroll/settings', 'icon' => 'fas fa-cog', 'is_visible' => 1, 'sort_order' => 7],
                    ['name' => 'Update Payroll Settings', 'slug' => 'admin/payroll/settings-update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 8],
                    ['name' => 'Bulk Approve Payroll', 'slug' => 'admin/payroll/bulk-approve', 'icon' => 'fas fa-check-double', 'is_visible' => 1, 'sort_order' => 9],

                    // Payroll CRUD - Wildcard routes
                    ['name' => 'Show Payroll', 'slug' => 'admin/payroll/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 10],
                    ['name' => 'Edit Payroll', 'slug' => 'admin/payroll/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 11],
                    ['name' => 'Update Payroll', 'slug' => 'admin/payroll/{encryptedId}/update', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 12],
                    ['name' => 'Process Payroll', 'slug' => 'admin/payroll/{encryptedId}/process', 'icon' => 'fas fa-cog', 'is_visible' => 0, 'sort_order' => 13],
                    ['name' => 'Mark as Paid', 'slug' => 'admin/payroll/{encryptedId}/mark-as-paid', 'icon' => 'fas fa-check-circle', 'is_visible' => 0, 'sort_order' => 14],
                    ['name' => 'Cancel Payroll', 'slug' => 'admin/payroll/{encryptedId}/cancel', 'icon' => 'fas fa-ban', 'is_visible' => 0, 'sort_order' => 15],
                    ['name' => 'Delete Payroll', 'slug' => 'admin/payroll/{encryptedId}/destroy', 'icon' => '', 'is_visible' => false, 'sort_order' => 16],

                    // Employee Payroll Configurations
                    ['name' => 'Employee Payroll Config Index', 'slug' => 'admin/payroll/employee-configurations', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 17],
                    ['name' => 'Edit Employee Config', 'slug' => 'admin/payroll/employee-configurations/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 18],
                    ['name' => 'Update Employee Config', 'slug' => 'admin/payroll/employee-configurations/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 19],
                    ['name' => 'Set Current Salary', 'slug' => 'admin/payroll/employee-configurations/{encryptedId}/set-current/{employeeSalary?}', 'icon' => 'fas fa-dollar-sign', 'is_visible' => 0, 'sort_order' => 20],
                    ['name' => 'Create Employee Salary', 'slug' => 'admin/payroll/employee-configurations/{encryptedId}/create-salary', 'icon' => 'fas fa-plus-circle', 'is_visible' => 0, 'sort_order' => 21],

                    // Alternative Employee Payroll Configurations Route
                    ['name' => 'Edit Alt Employee Config', 'slug' => 'admin/employee-payroll-configurations/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 22],
                    ['name' => 'Update Alt Employee Config', 'slug' => 'admin/employee-payroll-configurations/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 23],
                    ['name' => 'Update Employee Salary', 'slug' => 'admin/employee-payroll-configurations/{encryptedId}/update-salary', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 24],

                    // Beneficiary Badges (Allowances/Deductions)
                    ['name' => 'Allowances & Deductions', 'slug' => 'admin/payroll/beneficiary-badges/index', 'icon' => 'fas fa-list', 'is_visible' => 1, 'sort_order' => 25],
                    ['name' => 'Create Badge', 'slug' => 'admin/payroll/beneficiary-badges/create', 'icon' => 'fas fa-plus-circle', 'is_visible' => 1, 'sort_order' => 26],
                    ['name' => 'Store Badge', 'slug' => 'admin/payroll/beneficiary-badges/store', 'icon' => 'fas fa-save', 'is_visible' => 0, 'sort_order' => 27],
                    ['name' => 'Show Badge', 'slug' => 'admin/payroll/beneficiary-badges/{encryptedId}', 'icon' => 'fas fa-eye', 'is_visible' => 0, 'sort_order' => 28],
                    ['name' => 'Edit Badge', 'slug' => 'admin/payroll/beneficiary-badges/{encryptedId}/edit', 'icon' => 'fas fa-edit', 'is_visible' => 0, 'sort_order' => 29],
                    ['name' => 'Update Badge', 'slug' => 'admin/payroll/beneficiary-badges/{encryptedId}', 'icon' => 'fas fa-sync', 'is_visible' => 0, 'sort_order' => 30],
                    ['name' => 'Delete Badge', 'slug' => 'admin/payroll/beneficiary-badges/{encryptedId}/destroy', 'icon' => 'fas fa-trash', 'is_visible' => 0, 'sort_order' => 31],
                    ['name' => 'Apply Badge to All', 'slug' => 'admin/payroll/beneficiary-badges/{encryptedId}/apply-to-all', 'icon' => 'fas fa-check-double', 'is_visible' => 0, 'sort_order' => 32],
                    ['name' => 'API Apply Badge to All', 'slug' => 'admin/payroll/beneficiary-badges/{encryptedId}/api/apply-to-all', 'icon' => 'fas fa-check-double', 'is_visible' => 0, 'sort_order' => 33],

                    // PDF Payslip Routes
                    ['name' => 'List Payslips', 'slug' => 'employee/salary/payslips', 'icon' => 'fas fa-file-pdf', 'is_visible' => 1, 'sort_order' => 34],
                ]
            ],


        ];

        $childrenCount = 0;

        // First, get or create all parent slugs
        $parentIds = [];
        foreach ($sections as $section) {
            $parentSlug = DB::table('slugs')->updateOrInsert(
                ['slug' => $section['slug']],
                [
                    'name' => $section['name'],
                    'slug' => $section['slug'],
                    'icon' => $section['icon'],
                    'parent_id' => null,
                    'is_visible' => $section['is_visible'],
                    'sort_order' => $section['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $parent = DB::table('slugs')->where('slug', $section['slug'])->first();
            $parentIds[$section['slug']] = $parent->id;
        }

        // Now insert all children
        foreach ($sections as $section) {
            if (isset($section['children'])) {
                $childrenCount += count($section['children']);
                foreach ($section['children'] as $child) {
                    DB::table('slugs')->updateOrInsert(
                        ['slug' => $child['slug']],
                        [
                            'name' => $child['name'],
                            'slug' => $child['slug'],
                            'icon' => $child['icon'],
                            'parent_id' => $parentIds[$section['slug']],
                            'is_visible' => $child['is_visible'],
                            'sort_order' => $child['sort_order'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ All ' . count($sections) . ' sections and ' . $childrenCount . ' children seeded successfully!');
    }
}
