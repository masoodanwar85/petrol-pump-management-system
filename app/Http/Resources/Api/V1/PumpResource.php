<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PumpResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'unit_number' => $this->unit_number,
            'is_active' => $this->is_active,
            'nozzles' => NozzleResource::collection($this->whenLoaded('nozzles')),
        ];
    }
}
