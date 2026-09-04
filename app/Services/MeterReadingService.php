<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\MeterReading;
use App\Models\Nozzle;
use App\Models\Shift;
use App\Repositories\Contracts\MeterReadingRepositoryInterface;
use App\Repositories\Contracts\NozzleRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MeterReadingService
{
    public function __construct(
        private readonly MeterReadingRepositoryInterface $readings,
        private readonly NozzleRepositoryInterface $nozzles,
    ) {}

    /**
     * @param  array<int, array{nozzle_id: int, opening_reading: mixed}>  $payload
     * @return Collection<int, MeterReading>
     */
    public function recordOpenings(Shift $shift, array $payload): Collection
    {
        $this->assertShiftOpen($shift);

        $activeNozzles = $this->nozzles->active()->keyBy('id');
        $submitted = collect($payload)->keyBy('nozzle_id');

        $missing = $activeNozzles->keys()->diff($submitted->keys());
        if ($missing->isNotEmpty()) {
            throw new BusinessException(
                'Opening readings are required for every active nozzle.',
                errors: ['readings' => ['Missing nozzle IDs: '.$missing->implode(', ')]],
            );
        }

        $unknown = $submitted->keys()->diff($activeNozzles->keys());
        if ($unknown->isNotEmpty()) {
            throw new BusinessException(
                'One or more nozzles are invalid or inactive.',
                errors: ['readings' => ['Unknown nozzle IDs: '.$unknown->implode(', ')]],
            );
        }

        foreach ($submitted as $nozzleId => $row) {
            $this->recordOpening($shift, $activeNozzles->get($nozzleId), Decimal::of($row['opening_reading']));
        }

        return $this->readings->forShift($shift->id);
    }

    /**
     * @param  array{shift_id?: int, nozzle_id: int, opening_reading: mixed}  $data
     */
    public function recordSingleOpening(Shift $shift, array $data): MeterReading
    {
        $this->assertShiftOpen($shift);

        $nozzle = $this->nozzles->findOrFail((int) $data['nozzle_id']);

        if (! $nozzle->is_active) {
            throw new BusinessException('This nozzle is inactive.');
        }

        return $this->recordOpening($shift, $nozzle, Decimal::of($data['opening_reading']));
    }

    /**
     * @param  array<int, array{nozzle_id: int, closing_reading: mixed}>  $payload
     * @return Collection<int, MeterReading>
     */
    public function recordClosings(Shift $shift, array $payload): Collection
    {
        $this->assertShiftOpen($shift);

        $existing = $this->readings->forShift($shift->id)->keyBy('nozzle_id');

        if ($existing->isEmpty()) {
            throw new BusinessException('Opening readings must be recorded before closing readings.');
        }

        $submitted = collect($payload)->keyBy('nozzle_id');
        $missing = $existing->keys()->diff($submitted->keys());

        if ($missing->isNotEmpty()) {
            throw new BusinessException(
                'Closing readings are required for every nozzle that has an opening reading.',
                errors: ['readings' => ['Missing nozzle IDs: '.$missing->implode(', ')]],
            );
        }

        foreach ($submitted as $nozzleId => $row) {
            $reading = $existing->get($nozzleId);

            if (! $reading) {
                throw new BusinessException("No opening reading exists for nozzle {$nozzleId}.");
            }

            $this->applyClosing($reading, Decimal::of($row['closing_reading']));
        }

        return $this->readings->forShift($shift->id);
    }

    /**
     * @param  array{shift_id?: int, nozzle_id: int, closing_reading: mixed}  $data
     */
    public function recordSingleClosing(Shift $shift, array $data): MeterReading
    {
        $this->assertShiftOpen($shift);

        $reading = $this->readings->findForShiftNozzle($shift->id, (int) $data['nozzle_id']);

        if (! $reading) {
            throw new BusinessException('Opening reading must be recorded before the closing reading.');
        }

        return $this->applyClosing($reading, Decimal::of($data['closing_reading']));
    }

    public function assertAllClosingsPresent(Shift $shift): Collection
    {
        $readings = $this->readings->forShift($shift->id);

        if ($readings->isEmpty()) {
            throw new BusinessException('This shift has no opening meter readings.');
        }

        $incomplete = $readings->filter(fn (MeterReading $reading) => ! $reading->hasClosing());

        if ($incomplete->isNotEmpty()) {
            throw new BusinessException('Closing readings are required for every nozzle before the shift can be closed.');
        }

        return $readings;
    }

    private function recordOpening(Shift $shift, Nozzle $nozzle, string $opening): MeterReading
    {
        if (Decimal::isNegative($opening)) {
            throw new BusinessException("Opening reading for nozzle {$nozzle->id} cannot be negative.");
        }

        if ($nozzle->last_closing_reading !== null
            && Decimal::compare($opening, $nozzle->last_closing_reading) !== 0) {
            throw new BusinessException(
                "Opening reading for {$nozzle->label()} must match the previous closing reading of {$nozzle->last_closing_reading}.",
            );
        }

        $existing = $this->readings->findForShiftNozzle($shift->id, $nozzle->id);

        if ($existing) {
            return $this->readings->update($existing, [
                'opening_reading' => $opening,
                'opening_recorded_at' => Carbon::now(),
            ])->load(['nozzle.pump', 'nozzle.fuelType']);
        }

        return $this->readings->create([
            'shift_id' => $shift->id,
            'nozzle_id' => $nozzle->id,
            'opening_reading' => $opening,
            'opening_recorded_at' => Carbon::now(),
        ])->load(['nozzle.pump', 'nozzle.fuelType']);
    }

    private function applyClosing(MeterReading $reading, string $closing): MeterReading
    {
        if (Decimal::compare($closing, $reading->opening_reading) < 0) {
            throw new BusinessException('Closing reading must be greater than or equal to the opening reading.');
        }

        return $this->readings->update($reading, [
            'closing_reading' => $closing,
            'closing_recorded_at' => Carbon::now(),
        ])->load(['nozzle.pump', 'nozzle.fuelType']);
    }

    private function assertShiftOpen(Shift $shift): void
    {
        if (! $shift->isOpen()) {
            throw new BusinessException('Meter readings can only be recorded on an open shift.');
        }
    }
}
