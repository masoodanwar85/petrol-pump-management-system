<?php

namespace App\Repositories\Eloquent;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Repositories\Contracts\ShiftRepositoryInterface;

class ShiftRepository extends BaseRepository implements ShiftRepositoryInterface
{
    public function __construct(Shift $model)
    {
        parent::__construct($model);
    }

    public function currentOpen(): ?Shift
    {
        return $this->query()
            ->with(['user', 'meterReadings.nozzle.fuelType', 'meterReadings.nozzle.pump'])
            ->where('status', ShiftStatus::Open)
            ->latest('start_time')
            ->first();
    }

    public function currentOpenForUser(int $userId): ?Shift
    {
        return $this->query()
            ->with(['user', 'meterReadings.nozzle'])
            ->where('status', ShiftStatus::Open)
            ->where('user_id', $userId)
            ->latest('start_time')
            ->first();
    }
}
