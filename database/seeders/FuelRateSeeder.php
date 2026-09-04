<?php

namespace Database\Seeders;

use App\Enums\FuelTypeCode;
use App\Models\FuelRate;
use App\Models\FuelType;
use Illuminate\Database\Seeder;

class FuelRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            FuelTypeCode::Petrol->value => '272.50',
            FuelTypeCode::Diesel->value => '265.80',
        ];

        foreach ($rates as $code => $rate) {
            $fuelType = FuelType::query()->where('code', $code)->firstOrFail();

            FuelRate::query()->updateOrCreate(
                [
                    'fuel_type_id' => $fuelType->id,
                    'effective_from' => now()->subDay()->startOfDay(),
                ],
                [
                    'rate' => $rate,
                    'effective_to' => null,
                    'notes' => 'Initial seeded rate',
                ],
            );
        }
    }
}
