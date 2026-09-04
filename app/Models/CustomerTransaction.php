<?php

namespace App\Models;

use App\Enums\CustomerTransactionType;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\ImmutableWhenShiftClosed;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'customer_id',
    'type',
    'amount',
    'date',
    'shift_id',
    'fuel_type_id',
    'liters',
    'notes',
    'created_by',
])]
class CustomerTransaction extends Model
{
    use Auditable, ImmutableWhenShiftClosed, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => CustomerTransactionType::class,
            'amount' => 'decimal:2',
            'date' => 'date',
            'liters' => 'decimal:3',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
