<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable(['fuel_type_id', 'rate', 'effective_from', 'effective_to', 'notes'])]
class FuelRate extends Model
{
    use Auditable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
        ];
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function scopeActiveAt(Builder $query, Carbon|string $at): Builder
    {
        $moment = $at instanceof Carbon ? $at : Carbon::parse($at);

        return $query
            ->where('effective_from', '<=', $moment)
            ->where(function (Builder $builder) use ($moment): void {
                $builder->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $moment);
            });
    }

    public function isOpenEnded(): bool
    {
        return $this->effective_to === null;
    }
}
