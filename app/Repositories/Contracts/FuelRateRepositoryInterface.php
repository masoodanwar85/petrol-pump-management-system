<?php

namespace App\Repositories\Contracts;

use App\Models\FuelRate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * @extends BaseRepositoryInterface<FuelRate>
 */
interface FuelRateRepositoryInterface extends BaseRepositoryInterface
{
    public function activeAt(int $fuelTypeId, Carbon $at): ?FuelRate;

    /**
     * @return Collection<int, FuelRate>
     */
    public function overlapping(int $fuelTypeId, Carbon $from, ?Carbon $to, ?int $ignoreId = null): Collection;

    public function latestOpenEnded(int $fuelTypeId, ?int $ignoreId = null): ?FuelRate;
}
