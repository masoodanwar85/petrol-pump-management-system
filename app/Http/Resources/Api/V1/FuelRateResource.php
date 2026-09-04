<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelRateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fuel_type_id' => $this->fuel_type_id,
            'fuel_type' => new FuelTypeResource($this->whenLoaded('fuelType')),
            'rate' => $this->rate,
            'effective_from' => $this->effective_from,
            'effective_to' => $this->effective_to,
            'notes' => $this->notes,
            'is_open_ended' => $this->isOpenEnded(),
        ];
    }
}
