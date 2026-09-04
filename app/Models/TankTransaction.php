<?php

namespace App\Models;

use App\Enums\TankTransactionType;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tank_id',
    'type',
    'quantity_liters',
    'cost_per_liter',
    'total_cost',
    'date',
    'reference',
    'notes',
    'created_by',
])]
class TankTransaction extends Model
{
    use Auditable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => TankTransactionType::class,
            'quantity_liters' => 'decimal:3',
            'cost_per_liter' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
