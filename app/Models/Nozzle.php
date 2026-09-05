<?php

namespace App\Models;

use App\Enums\NozzleSide;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['pump_id', 'side', 'fuel_type_id', 'last_closing_reading', 'is_active'])]
class Nozzle extends Model
{
    use Auditable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'side' => NozzleSide::class,
            'last_closing_reading' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function pump(): BelongsTo
    {
        return $this->belongsTo(Pump::class);
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function sideLabel(): string
    {
        return $this->side instanceof NozzleSide
            ? $this->side->label()
            : 'Nozzle '.$this->side;
    }

    public function label(): string
    {
        $pumpName = $this->relationLoaded('pump') ? $this->pump?->name : 'Pump';

        return trim(($pumpName ?? 'Pump').' '.$this->sideLabel());
    }
}
