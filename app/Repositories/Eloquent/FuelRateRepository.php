<?php

namespace App\Repositories\Eloquent;

use App\Models\FuelRate;
use App\Repositories\Contracts\FuelRateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class FuelRateRepository extends BaseRepository implements FuelRateRepositoryInterface
{
    public function __construct(FuelRate $model)
    {
        parent::__construct($model);
    }

    public function activeAt(int $fuelTypeId, Carbon $at): ?FuelRate
    {
        return $this->query()
            ->where('fuel_type_id', $fuelTypeId)
            ->activeAt($at)
            ->orderByDesc('effective_from')
            ->first();
    }

    public function overlapping(int $fuelTypeId, Carbon $from, ?Carbon $to, ?int $ignoreId = null): Collection
    {
        $end = $to ?? Carbon::create(9999, 12, 31, 23, 59, 59);

        return $this->query()
            ->where('fuel_type_id', $fuelTypeId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('effective_from', '<', $end)
            ->where(function ($query) use ($from): void {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>', $from);
            })
            ->get();
    }

    public function latestOpenEnded(int $fuelTypeId, ?int $ignoreId = null): ?FuelRate
    {
        return $this->query()
            ->where('fuel_type_id', $fuelTypeId)
            ->whereNull('effective_to')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->orderByDesc('effective_from')
            ->first();
    }
}
