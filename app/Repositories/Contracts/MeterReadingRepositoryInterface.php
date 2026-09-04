<?php

namespace App\Repositories\Contracts;

use App\Models\MeterReading;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<MeterReading>
 */
interface MeterReadingRepositoryInterface extends BaseRepositoryInterface
{
    public function findForShiftNozzle(int $shiftId, int $nozzleId): ?MeterReading;

    /**
     * @return Collection<int, MeterReading>
     */
    public function forShift(int $shiftId): Collection;
}
