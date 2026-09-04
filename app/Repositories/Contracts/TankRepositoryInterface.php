<?php

namespace App\Repositories\Contracts;

use App\Models\Tank;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<Tank>
 */
interface TankRepositoryInterface extends BaseRepositoryInterface
{
    public function findByFuelType(int $fuelTypeId): ?Tank;

    /**
     * @return Collection<int, Tank>
     */
    public function belowThreshold(): Collection;
}
