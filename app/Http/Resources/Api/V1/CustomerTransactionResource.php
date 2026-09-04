<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'date' => $this->date?->toDateString(),
            'shift_id' => $this->shift_id,
            'fuel_type' => new FuelTypeResource($this->whenLoaded('fuelType')),
            'liters' => $this->liters,
            'notes' => $this->notes,
        ];
    }
}
