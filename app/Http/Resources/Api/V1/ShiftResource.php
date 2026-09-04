<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'closed_by' => new UserResource($this->whenLoaded('closedByUser')),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'expected_cash' => $this->expected_cash,
            'credit_sales_amount' => $this->credit_sales_amount,
            'notes' => $this->notes,
            'meter_readings' => MeterReadingResource::collection($this->whenLoaded('meterReadings')),
            'sales' => SaleResource::collection($this->whenLoaded('sales')),
        ];
    }
}
