<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the parent ID for 'superadmin'
        $parentId = DB::table('slugs')->where('slug', 'superadmin')->value('id');

        if (!$parentId) {
            $this->command->error('Parent slug "superadmin" not found. Please run the main SlugSeeder first.');
            return;
        }

        // Define Company slugs
        $companySlugs = [
            // Company Settings
            [
                'name' => 'Company Settings',
                'slug' => 'company/settings',
                'icon' => 'fas fa-cogs',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => 'Update Company Settings',
                'slug' => 'company/settings',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Save Employee ID Prefix',
                'slug' => 'company/settings/save-employee-id-prefix',
                'icon' => 'fas fa-id-card',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            // Company Role Management
            [
                'name' => 'Company Roles',
                'slug' => 'company-roles-list',
                'icon' => 'fas fa-user-tag',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => 'Create Company Role',
                'slug' => 'company-role-create',
                'icon' => 'fas fa-plus',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Store Company Role',
                'slug' => 'company-role-store',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Show Company Role',
                'slug' => 'company-role-show/{role}',
                'icon' => 'fas fa-eye',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Edit Company Role',
                'slug' => 'company-role-edit/{role}',
                'icon' => 'fas fa-edit',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Update Company Role',
                'slug' => 'company-role-update/{role}',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Delete Company Role',
                'slug' => 'company-role-delete/{role}',
                'icon' => 'fas fa-trash',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
        ];

        // Insert or update the slugs
        foreach ($companySlugs as $slug) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $slug['slug']],
                $slug
            );
        }

        $this->command->info('CompanySlugsSeeder completed successfully!');
        $this->command->info('Inserted ' . count($companySlugs) . ' Company slugs.');
    }
}