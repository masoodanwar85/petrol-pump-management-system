<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\FuelRate;
use App\Repositories\Contracts\FuelRateRepositoryInterface;
use App\Repositories\Contracts\FuelTypeRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FuelRateService
{
    public function __construct(
        private readonly FuelRateRepositoryInterface $rates,
        private readonly FuelTypeRepositoryInterface $fuelTypes,
    ) {}

    /**
     * @param  array{fuel_type_id: int, rate: mixed, effective_from: string, effective_to?: string|null, notes?: string|null}  $data
     */
    public function create(array $data): FuelRate
    {
        $this->fuelTypes->findOrFail((int) $data['fuel_type_id']);

        $from = Carbon::parse($data['effective_from']);
        $to = isset($data['effective_to']) && $data['effective_to'] !== null
            ? Carbon::parse($data['effective_to'])
            : null;

        if ($to !== null && $to->lte($from)) {
            throw new BusinessException('effective_to must be after effective_from.');
        }

        return DB::transaction(function () use ($data, $from, $to): FuelRate {
            $openEnded = $this->rates->latestOpenEnded((int) $data['fuel_type_id']);

            if ($openEnded && $to === null) {
                if ($from->lte($openEnded->effective_from)) {
                    throw new BusinessException('New rate must start after the current open-ended rate.');
                }

                $this->rates->update($openEnded, [
                    'effective_to' => $from->copy()->subSecond(),
                ]);
            }

            $overlaps = $this->rates->overlapping((int) $data['fuel_type_id'], $from, $to);

            if ($overlaps->isNotEmpty()) {
                throw new BusinessException(
                    'This rate overlaps an existing rate for the same fuel type. Only one active rate is allowed at a time.',
                );
            }

            return $this->rates->create([
                'fuel_type_id' => $data['fuel_type_id'],
                'rate' => $data['rate'],
                'effective_from' => $from,
                'effective_to' => $to,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function rateAt(int $fuelTypeId, Carbon $at): FuelRate
    {
        $rate = $this->rates->activeAt($fuelTypeId, $at);

        if (! $rate) {
            throw new BusinessException('No fuel rate is configured for this fuel type at the requested time.');
        }

        return $rate;
    }
}
