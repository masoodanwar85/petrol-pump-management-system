<?php

namespace App\Repositories\Eloquent;

use App\Models\Tank;
use App\Repositories\Contracts\TankRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TankRepository extends BaseRepository implements TankRepositoryInterface
{
    public function __construct(Tank $model)
    {
        parent::__construct($model);
    }

    public function findByFuelType(int $fuelTypeId): ?Tank
    {
        return $this->query()->where('fuel_type_id', $fuelTypeId)->first();
    }

    public function all(array $with = []): Collection
    {
        return $this->query()->with($with !== [] ? $with : ['fuelType'])->orderBy('id')->get();
    }

    public function belowThreshold(): Collection
    {
        return $this->query()
            ->with('fuelType')
            ->whereColumn('current_stock', '<=', 'low_level_threshold')
            ->get();
    }
}
