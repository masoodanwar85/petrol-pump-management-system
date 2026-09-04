<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\ImmutableWhenShiftClosed;
use App\Support\Decimal;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'shift_id',
    'nozzle_id',
    'opening_reading',
    'closing_reading',
    'opening_recorded_at',
    'closing_recorded_at',
])]
class MeterReading extends Model
{
    use Auditable, ImmutableWhenShiftClosed, SoftDeletes;

    protected function casts(): array
    {
        return [
            'opening_reading' => 'decimal:3',
            'closing_reading' => 'decimal:3',
            'opening_recorded_at' => 'datetime',
            'closing_recorded_at' => 'datetime',
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

    public function hasClosing(): bool
    {
        return $this->closing_reading !== null;
    }

    public function litersSold(): string
    {
        if (! $this->hasClosing()) {
            return '0.000';
        }

        return Decimal::subtract($this->closing_reading, $this->opening_reading);
    }
}
