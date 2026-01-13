<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get parent IDs
        $hrParentId = DB::table('slugs')->where('slug', 'hr')->value('id');
        $profileParentId = DB::table('slugs')->where('slug', 'profile')->value('id');

        if (!$hrParentId) {
            $this->command->error('Parent slug "hr" not found. Please run the main SlugSeeder first.');
            return;
        }

        if (!$profileParentId) {
            $this->command->error('Parent slug "profile" not found. Please run the main SlugSeeder first.');
            return;
        }

        // Define Employee Management slugs (under HR)
        $employeeManagementSlugs = [
            [
                'name' => 'Employees Management',
                'slug' => 'employees-management',
                'icon' => 'fas fa-users',
                'parent_id' => $hrParentId,
                'is_visible' => 1,
                'sort_order' => 20,
            ],
            [
                'name' => 'Create Employee',
                'slug' => 'employees-management/create',
                'icon' => 'fas fa-plus',
                'parent_id' => $hrParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Store Employee',
                'slug' => 'employees-management/post',
                'icon' => 'fas fa-save',
                'parent_id' => $hrParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'View Employee',
                'slug' => 'employees-management/{encryptedId}/view',
                'icon' => 'fas fa-eye',
                'parent_id' => $hrParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Edit Employee',
                'slug' => 'employees-management/{encryptedId}/edit',
                'icon' => 'fas fa-edit',
                'parent_id' => $hrParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Update Employee',
                'slug' => 'employees-management/{encryptedId}/update',
                'icon' => 'fas fa-save',
                'parent_id' => $hrParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Update Employee Role',
                'slug' => 'employees-management/{encryptedId}/role',
                'icon' => 'fas fa-user-tag',
                'parent_id' => $hrParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Toggle Employee Status',
                'slug' => 'employees-management/{encryptedId}/toggle-status',
                'icon' => 'fas fa-toggle-on',
                'parent_id' => $hrParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Get Next Employee Code',
                'slug' => 'employees-management/next-employee-code',
                'icon' => 'fas fa-hashtag',
                'parent_id' => $hrParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
        ];

        // Define Employee Profile slugs (under Profile)
        $employeeProfileSlugs = [
            [
                'name' => 'Employee Profile',
                'slug' => 'employee/profile',
                'icon' => 'fas fa-user',
                'parent_id' => $profileParentId,
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Update Employee Profile',
                'slug' => 'employee/profile/update',
                'icon' => 'fas fa-save',
                'parent_id' => $profileParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Update Employee Profile Image',
                'slug' => 'employee/profile/update-image',
                'icon' => 'fas fa-image',
                'parent_id' => $profileParentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Employee Colleagues',
                'slug' => 'employee/colleagues',
                'icon' => 'fas fa-users',
                'parent_id' => $profileParentId,
                'is_visible' => 1,
                'sort_order' => 2,
            ],
        ];

        // Combine all slugs
        $allSlugs = array_merge($employeeManagementSlugs, $employeeProfileSlugs);

        // Insert or update the slugs
        foreach ($allSlugs as $slug) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $slug['slug']],
                $slug
            );
        }

        $this->command->info('EmployeeSlugsSeeder completed successfully!');
        $this->command->info('Inserted ' . count($allSlugs) . ' Employee slugs.');
    }
}