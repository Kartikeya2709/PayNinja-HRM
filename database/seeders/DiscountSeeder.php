<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Discount;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            [
                'code' => 'EARLYBIRD10',
                'description' => '10% discount for early subscription',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
            ],
            [
                'code' => 'BULK15',
                'description' => '15% discount for 5+ packages',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => now()->addYear(),
            ],
            [
                'code' => 'LOYALTY50',
                'description' => '$50 off for returning customers',
                'discount_type' => 'fixed_amount',
                'discount_value' => 50.00,
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => now()->addYear(),
            ],
            [
                'code' => 'HOLIDAY20',
                'description' => '20% discount during holiday season',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'is_active' => true,
                'valid_from' => now()->addMonths(6),
                'valid_until' => now()->addMonths(7),
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::firstOrCreate(['code' => $discount['code']], $discount);
        }
    }
}