<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\ImmutableWhenShiftClosed;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'shift_id',
    'nozzle_id',
    'fuel_type_id',
    'liters_sold',
    'rate_per_liter',
    'total_amount',
    'cost_per_liter',
    'total_cost',
    'profit',
])]
class Sale extends Model
{
    use Auditable, ImmutableWhenShiftClosed, SoftDeletes;

    protected function casts(): array
    {
        return [
            'liters_sold' => 'decimal:3',
            'rate_per_liter' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'cost_per_liter' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function nozzle(): BelongsTo
    {
        return $this->belongsTo(Nozzle::class);
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }
}
