<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Support\Decimal;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'fuel_type_id',
    'capacity',
    'opening_stock',
    'current_stock',
    'weighted_avg_cost',
    'low_level_threshold',
    'is_active',
])]
class Tank extends Model
{
    use Auditable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:3',
            'opening_stock' => 'decimal:3',
            'current_stock' => 'decimal:3',
            'weighted_avg_cost' => 'decimal:2',
            'low_level_threshold' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TankTransaction::class);
    }

    public function isBelowThreshold(): bool
    {
        return Decimal::compare($this->current_stock, $this->low_level_threshold) <= 0;
    }

    public function fillPercentage(): string
    {
        if (Decimal::isZero($this->capacity)) {
            return '0.00';
        }

        return Decimal::multiply(
            Decimal::divide($this->current_stock, $this->capacity, 6),
            '100',
            2,
        );
    }
}
