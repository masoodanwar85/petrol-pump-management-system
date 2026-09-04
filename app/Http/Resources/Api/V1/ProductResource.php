<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'current_stock' => $this->current_stock,
            'is_active' => $this->is_active,
        ];
    }
}
