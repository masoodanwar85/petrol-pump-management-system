<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TankResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'fuel_type_id' => $this->fuel_type_id,
            'fuel_type' => new FuelTypeResource($this->whenLoaded('fuelType')),
            'capacity' => $this->capacity,
            'opening_stock' => $this->opening_stock,
            'current_stock' => $this->current_stock,
            'weighted_avg_cost' => $this->weighted_avg_cost,
            'low_level_threshold' => $this->low_level_threshold,
            'fill_percentage' => $this->fillPercentage(),
            'is_low' => $this->isBelowThreshold(),
            'is_active' => $this->is_active,
        ];
    }
}
