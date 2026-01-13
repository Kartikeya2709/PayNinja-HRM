<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadSlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the parent ID for 'lead'
        $parentId = DB::table('slugs')->where('slug', 'lead')->value('id');

        if (!$parentId) {
            $this->command->error('Parent slug "lead" not found. Please run the main SlugSeeder first.');
            return;
        }

        // Define Lead slugs
        $leadSlugs = [
            // Leads Management
            [
                'name' => 'Leads List',
                'slug' => 'company-admin/leads-list',
                'icon' => 'fas fa-list',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Create Lead',
                'slug' => 'company-admin/lead-create',
                'icon' => 'fas fa-plus',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Store Lead',
                'slug' => 'company-admin/lead-store',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Show Lead',
                'slug' => 'company-admin/lead-show/{lead}',
                'icon' => 'fas fa-eye',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Edit Lead',
                'slug' => 'company-admin/lead-edit/{lead}',
                'icon' => 'fas fa-edit',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Update Lead',
                'slug' => 'company-admin/lead-update/{lead}',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Delete Lead',
                'slug' => 'company-admin/lead-delete/{lead}',
                'icon' => 'fas fa-trash',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            // Module Access Management
            [
                'name' => 'Module Access',
                'slug' => 'company-admin/module-access',
                'icon' => 'fas fa-shield-alt',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Update Module Access',
                'slug' => 'company-admin/module-access',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
        ];

        // Insert or update the slugs
        foreach ($leadSlugs as $slug) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $slug['slug']],
                $slug
            );
        }

        $this->command->info('LeadSlugsSeeder completed successfully!');
        $this->command->info('Inserted ' . count($leadSlugs) . ' Lead slugs.');
    }
}