<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            [
                'name' => 'GST 18%',
                'rate' => 0.1800,
                'is_active' => true,
                'country' => 'IN',
                'state' => null,
            ],
            [
                'name' => 'VAT 20%',
                'rate' => 0.2000,
                'is_active' => true,
                'country' => 'GB',
                'state' => null,
            ],
            [
                'name' => 'Sales Tax 8.25%',
                'rate' => 0.0825,
                'is_active' => true,
                'country' => 'US',
                'state' => 'California',
            ],
            [
                'name' => 'Service Charge 10%',
                'rate' => 0.1000,
                'is_active' => true,
                'country' => null,
                'state' => null,
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate(['name' => $tax['name']], $tax);
        }
    }
}