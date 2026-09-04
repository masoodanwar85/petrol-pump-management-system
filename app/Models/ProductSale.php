<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\ImmutableWhenShiftClosed;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'product_id',
    'shift_id',
    'customer_id',
    'quantity',
    'unit_price',
    'total_amount',
    'unit_cost',
    'total_cost',
    'profit',
    'date',
    'notes',
    'created_by',
])]
class ProductSale extends Model
{
    use Auditable, ImmutableWhenShiftClosed, SoftDeletes;

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'profit' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
