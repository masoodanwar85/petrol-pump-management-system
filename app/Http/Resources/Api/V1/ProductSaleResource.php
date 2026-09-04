<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'shift_id' => $this->shift_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_amount' => $this->total_amount,
            'unit_cost' => $this->unit_cost,
            'total_cost' => $this->total_cost,
            'profit' => $this->profit,
            'date' => $this->date?->toDateString(),
        ];
    }
}
