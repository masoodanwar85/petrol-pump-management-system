<?php

namespace Database\Seeders;

use App\Enums\FuelTypeCode;
use App\Models\FuelType;
use App\Models\Tank;
use Illuminate\Database\Seeder;

class TankSeeder extends Seeder
{
    public function run(): void
    {
        $tanks = [
            FuelTypeCode::Petrol->value => [
                'name' => 'Petrol Tank',
                'capacity' => '20000.000',
                'opening_stock' => '12000.000',
                'current_stock' => '12000.000',
                'weighted_avg_cost' => '255.00',
                'low_level_threshold' => '2000.000',
            ],
            FuelTypeCode::Diesel->value => [
                'name' => 'Diesel Tank',
                'capacity' => '15000.000',
                'opening_stock' => '8000.000',
                'current_stock' => '8000.000',
                'weighted_avg_cost' => '248.00',
                'low_level_threshold' => '1500.000',
            ],
        ];

        foreach ($tanks as $code => $attributes) {
            $fuelType = FuelType::query()->where('code', $code)->firstOrFail();

            Tank::query()->updateOrCreate(
                ['fuel_type_id' => $fuelType->id],
                $attributes,
            );
        }
    }
}
