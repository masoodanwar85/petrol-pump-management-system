<?php

namespace App\Repositories\Eloquent;

use App\Models\FuelType;
use App\Repositories\Contracts\FuelTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FuelTypeRepository extends BaseRepository implements FuelTypeRepositoryInterface
{
    public function __construct(FuelType $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?FuelType
    {
        return $this->query()->where('code', $code)->first();
    }

    public function active(): Collection
    {
        return $this->query()->where('is_active', true)->orderBy('name')->get();
    }
}
