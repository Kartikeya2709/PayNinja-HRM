<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FieldVisitSlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the parent ID for 'field_visit'
        $parentId = DB::table('slugs')->where('slug', 'field_visit')->value('id');

        if (!$parentId) {
            $this->command->error('Parent slug "field_visit" not found. Please run the main SlugSeeder first.');
            return;
        }

        // Define Field Visit child slugs
        $fieldVisitSlugs = [
            [
                'name' => 'Pending Field Visits',
                'slug' => 'field-visits/pending',
                'icon' => 'fas fa-clock',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Field Visits List',
                'slug' => 'field-visits-list',
                'icon' => 'fas fa-list',
                'parent_id' => $parentId,
                'is_visible' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Create Field Visit',
                'slug' => 'field-visit-create',
                'icon' => 'fas fa-plus',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Store Field Visit',
                'slug' => 'field-visits',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Show Field Visit',
                'slug' => 'field-visit-show/{field_visit}',
                'icon' => 'fas fa-eye',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Edit Field Visit',
                'slug' => 'field-visit-edit/{field_visit}',
                'icon' => 'fas fa-edit',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Update Field Visit',
                'slug' => 'field-visit-update/{field_visit}',
                'icon' => 'fas fa-save',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Delete Field Visit',
                'slug' => 'field-visit-delete/{field_visit}',
                'icon' => 'fas fa-trash',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Approve Field Visit',
                'slug' => 'field-visits/{fieldVisit}/approve',
                'icon' => 'fas fa-check',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Reject Field Visit',
                'slug' => 'field-visits/{fieldVisit}/reject',
                'icon' => 'fas fa-times',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Start Field Visit',
                'slug' => 'field-visits/{fieldVisit}/start',
                'icon' => 'fas fa-play',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
            [
                'name' => 'Complete Field Visit',
                'slug' => 'field-visits/{fieldVisit}/complete',
                'icon' => 'fas fa-check-circle',
                'parent_id' => $parentId,
                'is_visible' => 0,
                'sort_order' => 0,
            ],
        ];

        // Insert or update the slugs
        foreach ($fieldVisitSlugs as $slug) {
            DB::table('slugs')->updateOrInsert(
                ['slug' => $slug['slug']],
                $slug
            );
        }

        $this->command->info('FieldVisitSlugsSeeder completed successfully!');
        $this->command->info('Inserted ' . count($fieldVisitSlugs) . ' Field Visit slugs.');
    }
}