<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AuditLog;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This seeder is for populating initial audit log types or sample data
        // Since audit logs are created dynamically, we don't need to seed initial data
        // This seeder can be used for testing purposes or to populate sample audit logs

        // Example of how to create sample audit logs for testing:
        /*
        AuditLog::create([
            'user_id' => 1, // Assuming superadmin user exists
            'action_type' => 'created',
            'model_type' => 'App\\Models\\Package',
            'model_id' => 1,
            'old_values' => null,
            'new_values' => ['name' => 'Sample Package', 'description' => 'Sample Description'],
            'description' => 'Sample package creation for testing',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder/1.0',
        ]);
        */
    }
}
