<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TankTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tank_id' => $this->tank_id,
            'tank' => new TankResource($this->whenLoaded('tank')),
            'type' => $this->type,
            'quantity_liters' => $this->quantity_liters,
            'cost_per_liter' => $this->cost_per_liter,
            'total_cost' => $this->total_cost,
            'date' => $this->date?->toDateString(),
            'reference' => $this->reference,
            'notes' => $this->notes,
        ];
    }
}
