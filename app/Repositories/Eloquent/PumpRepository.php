<?php

namespace App\Repositories\Eloquent;

use App\Models\Pump;
use App\Repositories\Contracts\PumpRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PumpRepository extends BaseRepository implements PumpRepositoryInterface
{
    public function __construct(Pump $model)
    {
        parent::__construct($model);
    }

    public function all(array $with = []): Collection
    {
        return $this->query()
            ->with($with !== [] ? $with : ['nozzles.fuelType'])
            ->orderBy('unit_number')
            ->get();
    }
}
