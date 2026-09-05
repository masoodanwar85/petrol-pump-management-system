<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\NozzleSide;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NozzleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pump_id' => $this->pump_id,
            'pump' => new PumpResource($this->whenLoaded('pump')),
            'side' => $this->side instanceof NozzleSide ? $this->side->value : $this->side,
            'side_label' => $this->sideLabel(),
            'fuel_type_id' => $this->fuel_type_id,
            'fuel_type' => new FuelTypeResource($this->whenLoaded('fuelType')),
            'last_closing_reading' => $this->last_closing_reading,
            'label' => $this->label(),
            'is_active' => $this->is_active,
        ];
    }
}
