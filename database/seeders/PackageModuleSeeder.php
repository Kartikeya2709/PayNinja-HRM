<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageModule;

class PackageModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = \App\Models\Package::all();

        foreach ($packages as $package) {
            $modules = [
                [
                    'package_id' => $package->id,
                    'module_name' => 'Employee Management',
                    'has_access' => true,
                ],
                [
                    'package_id' => $package->id,
                    'module_name' => 'Attendance Tracking',
                    'has_access' => true,
                ],
                [
                    'package_id' => $package->id,
                    'module_name' => 'Leave Management',
                    'has_access' => true,
                ],
                [
                    'package_id' => $package->id,
                    'module_name' => 'Payroll Processing',
                    'has_access' => in_array($package->name, ['Professional Package', 'Enterprise Package']),
                ],
                [
                    'package_id' => $package->id,
                    'module_name' => 'Performance Reviews',
                    'has_access' => in_array($package->name, ['Professional Package', 'Enterprise Package']),
                ],
                [
                    'package_id' => $package->id,
                    'module_name' => 'Recruitment',
                    'has_access' => in_array($package->name, ['Enterprise Package']),
                ],
                [
                    'package_id' => $package->id,
                    'module_name' => 'Asset Management',
                    'has_access' => in_array($package->name, ['Professional Package', 'Enterprise Package']),
                ],
                [
                    'package_id' => $package->id,
                    'module_name' => 'Reports & Analytics',
                    'has_access' => in_array($package->name, ['Professional Package', 'Enterprise Package']),
                ],
            ];

            foreach ($modules as $module) {
                PackageModule::firstOrCreate(
                    ['package_id' => $module['package_id'], 'module_name' => $module['module_name']],
                    $module
                );
            }
        }
    }
}