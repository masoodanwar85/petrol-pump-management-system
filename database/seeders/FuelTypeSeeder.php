<?php

namespace Database\Seeders;

use App\Enums\FuelTypeCode;
use App\Models\FuelType;
use Illuminate\Database\Seeder;

class FuelTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FuelTypeCode::cases() as $code) {
            FuelType::query()->updateOrCreate(
                ['code' => $code->value],
                [
                    'name' => $code->label(),
                    'unit' => 'L',
                    'is_active' => true,
                ],
            );
        }
    }
}
