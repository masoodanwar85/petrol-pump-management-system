<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shift_id' => $this->shift_id,
            'nozzle_id' => $this->nozzle_id,
            'nozzle' => new NozzleResource($this->whenLoaded('nozzle')),
            'fuel_type_id' => $this->fuel_type_id,
            'fuel_type' => new FuelTypeResource($this->whenLoaded('fuelType')),
            'liters_sold' => $this->liters_sold,
            'rate_per_liter' => $this->rate_per_liter,
            'total_amount' => $this->total_amount,
            'cost_per_liter' => $this->cost_per_liter,
            'total_cost' => $this->total_cost,
            'profit' => $this->profit,
        ];
    }
}
