<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollSlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get parent payroll slug
        $payrollParent = DB::table('slugs')->where('slug', 'payroll')->first();

        if (!$payrollParent) {
            $this->command->error('Payroll parent slug not found. Please run SlugSeeder first.');
            return;
        }

        // Payroll Slugs
        $slugs = [
            // Employee Payroll Management
            [
                'name' => 'Employee Payroll List',
                'slug' => 'employee/payroll/',
                'icon' => 'fas fa-list',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'Employee Payroll Show',
                'slug' => 'employee/payroll/{payroll}',
                'icon' => 'fas fa-eye',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 2,
            ],
            [
                'name' => 'Employee Payroll Download',
                'slug' => 'employee/payroll/{payroll}/download',
                'icon' => 'fas fa-download',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 3,
            ],
            
            // Admin Payroll Management
            [
                'name' => 'Payroll Index',
                'slug' => 'admin/payroll/index',
                'icon' => 'fas fa-list',
                'parent_id' => $payrollParent->id,
                'is_visible' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'Payroll Store',
                'slug' => 'admin/payroll',
                'icon' => 'fas fa-save',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 5,
            ],
            [
                'name' => 'Payroll Create',
                'slug' => 'admin/payroll/create',
                'icon' => 'fas fa-plus-circle',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 6,
            ],
            [
                'name' => 'Payroll Show',
                'slug' => 'admin/payroll/{payroll}',
                'icon' => 'fas fa-eye',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 7,
            ],
            [
                'name' => 'Payroll Edit',
                'slug' => 'admin/payroll/{payroll}/edit',
                'icon' => 'fas fa-edit',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 8,
            ],
            [
                'name' => 'Payroll Update',
                'slug' => 'admin/payroll/{payroll}/update',
                'icon' => 'fas fa-sync',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 9,
            ],
            [
                'name' => 'Payroll Process',
                'slug' => 'admin/payroll/{payroll}/process',
                'icon' => 'fas fa-cog',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 10,
            ],
            [
                'name' => 'Payroll Mark as Paid',
                'slug' => 'admin/payroll/{payroll}/mark-as-paid',
                'icon' => 'fas fa-check-circle',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 11,
            ],
            [
                'name' => 'Payroll Cancel',
                'slug' => 'admin/payroll/{payroll}/cancel',
                'icon' => 'fas fa-times-circle',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 12,
            ],
            [
                'name' => 'Payroll Destroy',
                'slug' => 'admin/payroll/{payroll}/destroy',
                'icon' => 'fas fa-trash',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 13,
            ],
            [
                'name' => 'Payroll Bulk Approve',
                'slug' => 'admin/payroll/bulk-approve',
                'icon' => 'fas fa-check-double',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 14,
            ],
            
            // Payroll Settings
            [
                'name' => 'Payroll Settings Edit',
                'slug' => 'admin/payroll/settings',
                'icon' => 'fas fa-cog',
                'parent_id' => $payrollParent->id,
                'is_visible' => 1,
                'sort_order' => 15,
            ],
            [
                'name' => 'Payroll Settings Update',
                'slug' => 'admin/payroll/settings/update',
                'icon' => 'fas fa-sync',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 16,
            ],
            
            // Employee Payroll Configurations
            [
                'name' => 'Employee Configurations Index',
                'slug' => 'admin/payroll/employee-configurations',
                'icon' => 'fas fa-list',
                'parent_id' => $payrollParent->id,
                'is_visible' => 1,
                'sort_order' => 17,
            ],
            [
                'name' => 'Employee Configurations Edit',
                'slug' => 'admin/payroll/employee-configurations/{employee}/edit',
                'icon' => 'fas fa-edit',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 18,
            ],
            [
                'name' => 'Employee Configurations Update',
                'slug' => 'admin/payroll/employee-configurations/{employee}',
                'icon' => 'fas fa-sync',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 19,
            ],
            [
                'name' => 'Employee Configurations Set Current',
                'slug' => 'admin/payroll/employee-configurations/{employee}/set-current/{employeeSalary?}',
                'icon' => 'fas fa-star',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 20,
            ],
            [
                'name' => 'Employee Configurations Create Salary',
                'slug' => 'admin/payroll/employee-configurations/{employee}/create-salary',
                'icon' => 'fas fa-plus-circle',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 21,
            ],
            [
                'name' => 'Employee Configurations Update Salary',
                'slug' => 'admin/employee-payroll-configurations/{employee}/update-salary',
                'icon' => 'fas fa-sync',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 22,
            ],
            [
                'name' => 'Employee Payroll Configurations Edit',
                'slug' => 'admin/employee-payroll-configurations/{employee}/edit',
                'icon' => 'fas fa-edit',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 24,
            ],
            [
                'name' => 'Employee Payroll Configurations Update',
                'slug' => 'admin/employee-payroll-configurations/{employee}',
                'icon' => 'fas fa-sync',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 25,
            ],
            // Beneficiary Badges (Allowances/Deductions)
            [
                'name' => 'Beneficiary Badges Index',
                'slug' => 'admin/payroll/beneficiary-badges/index',
                'icon' => 'fas fa-list',
                'parent_id' => $payrollParent->id,
                'is_visible' => 1,
                'sort_order' => 26,
            ],
            [
                'name' => 'Beneficiary Badges Create',
                'slug' => 'admin/payroll/beneficiary-badges/create',
                'icon' => 'fas fa-plus-circle',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 27,
            ],
            [
                'name' => 'Beneficiary Badges Store',
                'slug' => 'admin/payroll/beneficiary-badges/store',
                'icon' => 'fas fa-save',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 28,
            ],
            [
                'name' => 'Beneficiary Badges Show',
                'slug' => 'admin/payroll/beneficiary-badges/{beneficiaryBadge}',
                'icon' => 'fas fa-eye',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 29,
            ],
            [
                'name' => 'Beneficiary Badges Edit',
                'slug' => 'admin/payroll/beneficiary-badges/{beneficiaryBadge}/edit',
                'icon' => 'fas fa-edit',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 30,
            ],
            [
                'name' => 'Beneficiary Badges Update',
                'slug' => 'admin/payroll/beneficiary-badges/{beneficiaryBadge}',
                'icon' => 'fas fa-sync',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 31,
            ],
            [
                'name' => 'Beneficiary Badges Destroy',
                'slug' => 'admin/payroll/beneficiary-badges/{beneficiaryBadge}/destroy',
                'icon' => 'fas fa-trash',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 32,
            ],
            [
                'name' => 'Beneficiary Badges Apply to All',
                'slug' => 'admin/payroll/beneficiary-badges/{beneficiaryBadge}/apply-to-all',
                'icon' => 'fas fa-check-double',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 33,
            ],
            [
                'name' => 'Beneficiary Badges API Apply to All',
                'slug' => 'admin/payroll/beneficiary-badges/{beneficiaryBadge}/api/apply-to-all',
                'icon' => 'fas fa-check-double',
                'parent_id' => $payrollParent->id,
                'is_visible' => 0,
                'sort_order' => 34,
            ],
        ];

        // Insert or update slugs
        foreach ($slugs as $slug) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $slug['slug']],
                $slug
            );
        }

        $this->command->info('Payroll   '  .count($slugs) .' seeded successfully!');
    }
}
