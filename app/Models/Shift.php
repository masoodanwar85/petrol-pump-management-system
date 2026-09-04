<?php

namespace App\Models;

use App\Enums\ShiftStatus;
use App\Models\Concerns\Auditable;
use App\Models\Concerns\ImmutableWhenShiftClosed;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'start_time',
    'end_time',
    'status',
    'expected_cash',
    'credit_sales_amount',
    'notes',
    'closed_by',
])]
class Shift extends Model
{
    use Auditable, ImmutableWhenShiftClosed, SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'status' => ShiftStatus::class,
            'expected_cash' => 'decimal:2',
            'credit_sales_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function customerTransactions(): HasMany
    {
        return $this->hasMany(CustomerTransaction::class);
    }

    public function productSales(): HasMany
    {
        return $this->hasMany(ProductSale::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', ShiftStatus::Open);
    }

    public function isOpen(): bool
    {
        return $this->status === ShiftStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === ShiftStatus::Closed;
    }
}
