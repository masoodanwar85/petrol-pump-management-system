<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'vehicle_number' => $this->vehicle_number,
            'credit_limit' => $this->credit_limit,
            'outstanding' => $this->outstandingBalance(),
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];
    }
}
