<?php

namespace App\Models;

use App\Enums\ProductStockType;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['product_id', 'type', 'quantity', 'unit_cost', 'total_cost', 'date', 'notes', 'created_by'])]
class ProductStock extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'product_stock';

    protected function casts(): array
    {
        return [
            'type' => ProductStockType::class,
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
