<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        Customer::query()->updateOrCreate(
            ['phone' => '03219876543'],
            [
                'name' => 'Ahmed Logistics',
                'vehicle_number' => 'LES-2041',
                'credit_limit' => '150000.00',
                'is_active' => true,
            ],
        );

        Product::query()->updateOrCreate(
            ['sku' => 'OIL-1L'],
            [
                'name' => 'Engine Oil 1L',
                'unit' => 'bottle',
                'purchase_price' => '850.00',
                'selling_price' => '1100.00',
                'current_stock' => '24.000',
                'is_active' => true,
            ],
        );
    }
}
