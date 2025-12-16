<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('slugs')->truncate();

        // Define parent slugs (main modules)
        $parentSlugs = [
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Human Resources',
                'slug' => 'hr',
                'icon' => 'fas fa-users',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Attendance',
                'slug' => 'attendance',
                'icon' => 'fas fa-clock',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Payroll',
                'slug' => 'payroll',
                'icon' => 'fas fa-money-bill-wave',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'Leave Management',
                'slug' => 'leave',
                'icon' => 'fas fa-calendar-times',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => 'Assets',
                'slug' => 'asset',
                'icon' => 'fas fa-boxes',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => 'Reimbursements',
                'slug' => 'reimbursement',
                'icon' => 'fas fa-receipt',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => 'Announcements',
                'slug' => 'announcement',
                'icon' => 'fas fa-bullhorn',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 8,
            ],
            [
                'name' => 'Handbook',
                'slug' => 'handbook',
                'icon' => 'fas fa-book',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 9,
            ],
            [
                'name' => 'Field Visits',
                'slug' => 'field_visit',
                'icon' => 'fas fa-route',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 10,
            ],
            [
                'name' => 'Leads',
                'slug' => 'lead',
                'icon' => 'fas fa-user-plus',
                'parent_id' => null,
                'is_visible' => 1,
                'sort_order' => 11,
            ],
        ];

        // Insert parent slugs and get their IDs
        $parentIds = [];
        foreach ($parentSlugs as $slug) {
            $parentIds[$slug['slug']] = DB::table('slugs')->insertGetId($slug);
        }

        // Define child slugs (sub-modules)
        $childSlugs = [
            // HR Management
            [
                'name' => 'Employees',
                'slug' => 'employee',
                'icon' => 'fas fa-user-tie',
                'parent_id' => $parentIds['hr'],
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Departments',
                'slug' => 'department',
                'icon' => 'fas fa-building',
                'parent_id' => $parentIds['hr'],
                'is_visible' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Designations',
                'slug' => 'designation',
                'icon' => 'fas fa-id-badge',
                'parent_id' => $parentIds['hr'],
                'is_visible' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Teams',
                'slug' => 'team',
                'icon' => 'fas fa-users-cog',
                'parent_id' => $parentIds['hr'],
                'is_visible' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'Employment Types',
                'slug' => 'employment_type',
                'icon' => 'fas fa-briefcase',
                'parent_id' => $parentIds['hr'],
                'is_visible' => 1,
                'sort_order' => 5,
            ],

            // Attendance Management
            [
                'name' => 'Daily Attendance',
                'slug' => 'attendance_daily',
                'icon' => 'fas fa-calendar-day',
                'parent_id' => $parentIds['attendance'],
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Attendance Settings',
                'slug' => 'attendance_settings',
                'icon' => 'fas fa-cogs',
                'parent_id' => $parentIds['attendance'],
                'is_visible' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Regularization',
                'slug' => 'attendance_regularization',
                'icon' => 'fas fa-edit',
                'parent_id' => $parentIds['attendance'],
                'is_visible' => 1,
                'sort_order' => 3,
            ],

            // Payroll Management
            // [
            //     'name' => 'Salary Management',
            //     'slug' => 'payroll_salary',
            //     'icon' => 'fas fa-dollar-sign',
            //     'parent_id' => $parentIds['payroll'],
            //     'is_visible' => 1,
            //     'sort_order' => 1,
            // ],
            // [
            //     'name' => 'Payroll Processing',
            //     'slug' => 'payroll_processing',
            //     'icon' => 'fas fa-calculator',
            //     'parent_id' => $parentIds['payroll'],
            //     'is_visible' => 1,
            //     'sort_order' => 2,
            // ],
            // [
            //     'name' => 'Payroll Reports',
            //     'slug' => 'payroll_reports',
            //     'icon' => 'fas fa-chart-bar',
            //     'parent_id' => $parentIds['payroll'],
            //     'is_visible' => 1,
            //     'sort_order' => 3,
            // ],

            // Leave Management
            [
                'name' => 'Leave Types',
                'slug' => 'leave_types',
                'icon' => 'fas fa-list',
                'parent_id' => $parentIds['leave'],
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Leave Requests',
                'slug' => 'leave_requests',
                'icon' => 'fas fa-paper-plane',
                'parent_id' => $parentIds['leave'],
                'is_visible' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Leave Balances',
                'slug' => 'leave_balances',
                'icon' => 'fas fa-balance-scale',
                'parent_id' => $parentIds['leave'],
                'is_visible' => 1,
                'sort_order' => 3,
            ],

            // Asset Management
            [
                'name' => 'Asset Categories',
                'slug' => 'asset_categories',
                'icon' => 'fas fa-tags',
                'parent_id' => $parentIds['asset'],
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Asset Inventory',
                'slug' => 'asset_inventory',
                'icon' => 'fas fa-box',
                'parent_id' => $parentIds['asset'],
                'is_visible' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Asset Assignments',
                'slug' => 'asset_assignments',
                'icon' => 'fas fa-handshake',
                'parent_id' => $parentIds['asset'],
                'is_visible' => 1,
                'sort_order' => 3,
            ],
        ];

        // Insert child slugs
        foreach ($childSlugs as $slug) {
            DB::table('slugs')->insert($slug);
        }

        $this->command->info('Slug seeder completed successfully!');
        $this->command->info('Created ' . count($parentSlugs) . ' parent slugs and ' . count($childSlugs) . ' child slugs.');
    }
}
