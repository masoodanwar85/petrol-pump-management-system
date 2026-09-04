<?php

namespace App\Models;

use App\Enums\FuelTypeCode;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'code', 'unit', 'is_active'])]
class FuelType extends Model
{
    use Auditable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'code' => FuelTypeCode::class,
            'is_active' => 'boolean',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(FuelRate::class);
    }

    public function tank(): HasOne
    {
        return $this->hasOne(Tank::class);
    }

    public function nozzles(): HasMany
    {
        return $this->hasMany(Nozzle::class);
    }
}
