<?php

namespace App\Repositories\Eloquent;

use App\Models\Nozzle;
use App\Repositories\Contracts\NozzleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class NozzleRepository extends BaseRepository implements NozzleRepositoryInterface
{
    public function __construct(Nozzle $model)
    {
        parent::__construct($model);
    }

    public function all(array $with = []): Collection
    {
        return $this->query()
            ->with($with !== [] ? $with : ['pump', 'fuelType'])
            ->orderBy('pump_id')
            ->orderBy('side')
            ->get();
    }

    public function active(): Collection
    {
        return $this->query()
            ->with(['pump', 'fuelType'])
            ->where('is_active', true)
            ->orderBy('pump_id')
            ->orderBy('side')
            ->get();
    }
}
