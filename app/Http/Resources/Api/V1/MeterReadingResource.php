<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeterReadingResource extends JsonResource
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
            'opening_reading' => $this->opening_reading,
            'closing_reading' => $this->closing_reading,
            'liters_sold' => $this->hasClosing() ? $this->litersSold() : null,
            'opening_recorded_at' => $this->opening_recorded_at,
            'closing_recorded_at' => $this->closing_recorded_at,
        ];
    }
}
