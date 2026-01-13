<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResignationSlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the parent ID for 'hr' (Human Resources)
        $parentId = DB::table('slugs')->where('slug', 'hr')->value('id');

        if (!$parentId) {
            $this->command->error('Parent slug "hr" not found. Please run the main SlugSeeder first.');
            return;
        }

        // Define Resignation child slugs
        $resignationSlugs = [
            [
                'name' => 'Resignations',
                'slug' => 'resignations',
                'icon' => 'fas fa-user-times',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 25,
            ],
            // Employee Resignations
            [
                'name' => 'My Resignations',
                'slug' => 'resignations/my-resignations',
                'icon' => 'fas fa-list',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 26,
            ],
            [
                'name' => 'Create My Resignation',
                'slug' => 'resignations/my-resignations/create',
                'icon' => 'fas fa-plus',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Store My Resignation',
                'slug' => 'resignations/my-resignations',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Show My Resignation',
                'slug' => 'resignations/my-resignations/{my_resignation}',
                'icon' => 'fas fa-eye',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Edit My Resignation',
                'slug' => 'resignations/my-resignations/{my_resignation}/edit',
                'icon' => 'fas fa-edit',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Update My Resignation',
                'slug' => 'resignations/my-resignations/{my_resignation}',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Withdraw My Resignation',
                'slug' => 'resignations/my-resignations/{resignation}/withdraw',
                'icon' => 'fas fa-undo',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            // Admin Resignation Management
            [
                'name' => 'Admin Resignations',
                'slug' => 'resignations',
                'icon' => 'fas fa-user-cog',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 27,
            ],
            [
                'name' => 'Show Resignation',
                'slug' => 'resignations/{resignation}',
                'icon' => 'fas fa-eye',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Approve Resignation',
                'slug' => 'resignations/{resignation}/approve',
                'icon' => 'fas fa-check',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Reject Resignation',
                'slug' => 'resignations/{resignation}/reject',
                'icon' => 'fas fa-times',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            // Exit Process Management
            [
                'name' => 'Complete Exit Interview',
                'slug' => 'resignations/{resignation}/complete-exit-interview',
                'icon' => 'fas fa-comments',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Complete Handover',
                'slug' => 'resignations/{resignation}/complete-handover',
                'icon' => 'fas fa-handshake',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Assigned Assets',
                'slug' => 'resignations/{resignation}/assigned-assets',
                'icon' => 'fas fa-boxes',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Mark Assets Returned',
                'slug' => 'resignations/{resignation}/mark-assets-returned',
                'icon' => 'fas fa-check-circle',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Complete Final Settlement',
                'slug' => 'resignations/{resignation}/complete-final-settlement',
                'icon' => 'fas fa-money-bill-wave',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
        ];

        // Insert or update the slugs
        foreach ($resignationSlugs as $slug) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $slug['slug']],
                $slug
            );
        }

        $this->command->info('ResignationSlugsSeeder completed successfully!');
        $this->command->info('Inserted ' . count($resignationSlugs) . ' Resignation slugs.');
    }
}