<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'code', 'unit_number', 'is_active'])]
class Pump extends Model
{
    use Auditable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'unit_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function nozzles(): HasMany
    {
        return $this->hasMany(Nozzle::class);
    }
}
