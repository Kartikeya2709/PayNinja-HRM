<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic Package',
                'description' => 'Essential HR features for small teams',
                'pricing_type' => 'subscription',
                'base_price' => 99.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'modules' => [
                    'dashboard' => true,
                    'employee' => true,
                    'attendance' => true,
                    'leave' => true,
                    'announcement' => true,
                    'handbook' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Professional Package',
                'description' => 'Advanced HR management for growing companies',
                'pricing_type' => 'subscription',
                'base_price' => 199.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'modules' => [
                    'dashboard' => true,
                    'hr' => true,
                    'attendance' => true,
                    'payroll' => true,
                    'leave' => true,
                    'asset' => true,
                    'reimbursement' => true,
                    'announcement' => true,
                    'handbook' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise Package',
                'description' => 'Complete HR suite for large organizations',
                'pricing_type' => 'subscription',
                'base_price' => 399.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'modules' => [
                    'dashboard' => true,
                    'hr' => true,
                    'attendance' => true,
                    'payroll' => true,
                    'leave' => true,
                    'asset' => true,
                    'reimbursement' => true,
                    'announcement' => true,
                    'handbook' => true,
                    'field_visit' => true,
                    'lead' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'One-Time Setup',
                'description' => 'Initial setup and configuration service',
                'pricing_type' => 'one_time',
                'base_price' => 499.99,
                'currency' => 'USD',
                'billing_cycle' => null,
                'modules' => [
                    'dashboard' => true,
                    'hr' => true,
                    'attendance' => true,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::firstOrCreate(['name' => $package['name']], $package);
        }
    }
}