<?php

namespace App\Services;

use App\Repositories\Contracts\TankRepositoryInterface;

class AlertService
{
    public function __construct(
        private readonly TankRepositoryInterface $tanks,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function active(): array
    {
        return $this->tanks->belowThreshold()
            ->map(function ($tank): array {
                return [
                    'type' => 'low_tank_level',
                    'severity' => 'warning',
                    'tank_id' => $tank->id,
                    'tank_name' => $tank->name,
                    'fuel_type' => $tank->fuelType?->name,
                    'current_stock' => $tank->current_stock,
                    'low_level_threshold' => $tank->low_level_threshold,
                    'capacity' => $tank->capacity,
                    'message' => "{$tank->name} is at or below the low-level threshold ({$tank->current_stock} L / {$tank->low_level_threshold} L).",
                ];
            })
            ->values()
            ->all();
    }
}
