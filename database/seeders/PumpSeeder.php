<?php

namespace Database\Seeders;

use App\Enums\FuelTypeCode;
use App\Enums\NozzleSide;
use App\Models\FuelType;
use App\Models\Nozzle;
use App\Models\Pump;
use Illuminate\Database\Seeder;

class PumpSeeder extends Seeder
{
    public function run(): void
    {
        $petrol = FuelType::query()->where('code', FuelTypeCode::Petrol)->firstOrFail();
        $diesel = FuelType::query()->where('code', FuelTypeCode::Diesel)->firstOrFail();

        $layout = [
            [
                'name' => 'Unit 1',
                'code' => 'U1',
                'unit_number' => 1,
                'nozzles' => [
                    NozzleSide::A->value => $petrol->id,
                    NozzleSide::B->value => $petrol->id,
                ],
            ],
            [
                'name' => 'Unit 2',
                'code' => 'U2',
                'unit_number' => 2,
                'nozzles' => [
                    NozzleSide::A->value => $petrol->id,
                    NozzleSide::B->value => $diesel->id,
                ],
            ],
            [
                'name' => 'Unit 3',
                'code' => 'U3',
                'unit_number' => 3,
                'nozzles' => [
                    NozzleSide::A->value => $diesel->id,
                    NozzleSide::B->value => $diesel->id,
                ],
            ],
        ];

        foreach ($layout as $unit) {
            $pump = Pump::query()->updateOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'unit_number' => $unit['unit_number'],
                    'is_active' => true,
                ],
            );

            foreach ($unit['nozzles'] as $side => $fuelTypeId) {
                Nozzle::query()->updateOrCreate(
                    [
                        'pump_id' => $pump->id,
                        'side' => $side,
                    ],
                    [
                        'fuel_type_id' => $fuelTypeId,
                        'last_closing_reading' => null,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
