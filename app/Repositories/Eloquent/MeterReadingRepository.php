<?php

namespace App\Repositories\Eloquent;

use App\Models\MeterReading;
use App\Repositories\Contracts\MeterReadingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MeterReadingRepository extends BaseRepository implements MeterReadingRepositoryInterface
{
    public function __construct(MeterReading $model)
    {
        parent::__construct($model);
    }

    public function findForShiftNozzle(int $shiftId, int $nozzleId): ?MeterReading
    {
        return $this->query()
            ->where('shift_id', $shiftId)
            ->where('nozzle_id', $nozzleId)
            ->first();
    }

    public function forShift(int $shiftId): Collection
    {
        return $this->query()
            ->with(['nozzle.pump', 'nozzle.fuelType'])
            ->where('shift_id', $shiftId)
            ->get();
    }
}
