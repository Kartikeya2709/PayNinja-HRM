<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReimbursementSlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the parent ID for 'reimbursement'
        $parentId = DB::table('slugs')->where('slug', 'reimbursement')->value('id');

        if (!$parentId) {
            $this->command->error('Parent slug "reimbursement" not found. Please run the main SlugSeeder first.');
            return;
        }

        // Define Reimbursement child slugs
        $reimbursementSlugs = [
            [
                'name' => 'Reimbursements List',
                'slug' => 'reimbursements',
                'icon' => 'fas fa-list',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Create Reimbursement',
                'slug' => 'reimbursements/create',
                'icon' => 'fas fa-plus',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Store Reimbursement',
                'slug' => 'reimbursements/post',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Show Reimbursement',
                'slug' => 'reimbursements/{reimbursement}',
                'icon' => 'fas fa-eye',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Approve Reimbursement',
                'slug' => 'reimbursements/{reimbursement}/approve',
                'icon' => 'fas fa-check',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Approve Reimbursement by Reporter',
                'slug' => 'reimbursements/{reimbursement}/approve/reporter',
                'icon' => 'fas fa-user-check',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Reject Reimbursement',
                'slug' => 'reimbursements/{reimbursement}/reject',
                'icon' => 'fas fa-times',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Pending Reimbursements',
                'slug' => 'reimbursements/pending',
                'icon' => 'fas fa-clock',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 2,
            ],
        ];

        // Insert or update the slugs
        foreach ($reimbursementSlugs as $slug) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $slug['slug']],
                $slug
            );
        }

        $this->command->info('ReimbursementSlugsSeeder completed successfully!');
        $this->command->info('Inserted ' . count($reimbursementSlugs) . ' Reimbursement slugs.');
    }
}