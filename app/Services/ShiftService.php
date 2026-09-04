<?php

namespace App\Services;

use App\Enums\CustomerTransactionType;
use App\Enums\ShiftStatus;
use App\Exceptions\BusinessException;
use App\Models\MeterReading;
use App\Models\Shift;
use App\Models\User;
use App\Repositories\Contracts\MeterReadingRepositoryInterface;
use App\Repositories\Contracts\NozzleRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\TankRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShiftService
{
    public function __construct(
        private readonly ShiftRepositoryInterface $shifts,
        private readonly MeterReadingRepositoryInterface $readings,
        private readonly NozzleRepositoryInterface $nozzles,
        private readonly SaleRepositoryInterface $sales,
        private readonly TankRepositoryInterface $tanks,
        private readonly MeterReadingService $meterReadings,
        private readonly FuelRateService $fuelRates,
        private readonly TankService $tankService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function start(User $user, array $data): Shift
    {
        if ($this->shifts->currentOpen()) {
            throw new BusinessException('Another shift is already open. Close it before starting a new one.');
        }

        $readings = $data['readings'] ?? [];

        if ($readings === []) {
            throw new BusinessException('Opening meter readings are required to start a shift.');
        }

        return DB::transaction(function () use ($user, $data, $readings): Shift {
            $shift = $this->shifts->create([
                'user_id' => $user->id,
                'start_time' => $data['start_time'] ?? Carbon::now(),
                'status' => ShiftStatus::Open,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->meterReadings->recordOpenings($shift, $readings);

            return $shift->load(['user', 'meterReadings.nozzle.pump', 'meterReadings.nozzle.fuelType']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function end(Shift $shift, User $user, array $data): Shift
    {
        if (! $shift->isOpen()) {
            throw new BusinessException('This shift is already closed.');
        }

        return DB::transaction(function () use ($shift, $user, $data): Shift {
            $locked = Shift::query()->lockForUpdate()->findOrFail($shift->id);

            if (! $locked->isOpen()) {
                throw new BusinessException('This shift is already closed.');
            }

            if (! empty($data['readings'])) {
                $this->meterReadings->recordClosings($locked, $data['readings']);
            }

            $readings = $this->meterReadings->assertAllClosingsPresent($locked);
            $this->assertCreditLitersDoNotExceedSales($locked, $readings);
            $this->generateSalesAndDeductStock($locked, $readings);

            $fuelSalesTotal = Decimal::money((string) $locked->sales()->sum('total_amount'));
            $creditSales = Decimal::money((string) $locked->customerTransactions()
                ->where('type', CustomerTransactionType::Sale)
                ->sum('amount'));
            $expectedCash = Decimal::subtract($fuelSalesTotal, $creditSales, 2);

            $this->shifts->update($locked, [
                'end_time' => $data['end_time'] ?? Carbon::now(),
                'status' => ShiftStatus::Closed,
                'expected_cash' => $expectedCash,
                'credit_sales_amount' => $creditSales,
                'notes' => $data['notes'] ?? $locked->notes,
                'closed_by' => $user->id,
            ]);

            return $locked->fresh([
                'user',
                'closedByUser',
                'meterReadings.nozzle.pump',
                'meterReadings.nozzle.fuelType',
                'sales.fuelType',
                'sales.nozzle',
            ]);
        });
    }

    /**
     * @param  Collection<int, MeterReading>  $readings
     */
    private function generateSalesAndDeductStock(Shift $shift, $readings): void
    {
        $litersByFuelType = [];

        foreach ($readings as $reading) {
            $nozzle = $reading->nozzle;
            $liters = $reading->litersSold();
            $rate = $this->fuelRates->rateAt($nozzle->fuel_type_id, $shift->start_time);
            $tank = $this->tanks->findByFuelType($nozzle->fuel_type_id);

            if (! $tank) {
                throw new BusinessException("No tank is configured for fuel type {$nozzle->fuel_type_id}.");
            }

            $amount = Decimal::money(Decimal::multiply($liters, $rate->rate, 4));
            $costPerLiter = Decimal::money($tank->weighted_avg_cost);
            $totalCost = Decimal::money(Decimal::multiply($liters, $costPerLiter, 4));
            $profit = Decimal::subtract($amount, $totalCost, 2);

            $this->sales->create([
                'shift_id' => $shift->id,
                'nozzle_id' => $nozzle->id,
                'fuel_type_id' => $nozzle->fuel_type_id,
                'liters_sold' => $liters,
                'rate_per_liter' => $rate->rate,
                'total_amount' => $amount,
                'cost_per_liter' => $costPerLiter,
                'total_cost' => $totalCost,
                'profit' => $profit,
            ]);

            $nozzle->update([
                'last_closing_reading' => $reading->closing_reading,
            ]);

            $fuelTypeId = $nozzle->fuel_type_id;
            $litersByFuelType[$fuelTypeId] = Decimal::add($litersByFuelType[$fuelTypeId] ?? '0', $liters);
        }

        foreach ($litersByFuelType as $fuelTypeId => $liters) {
            $tank = $this->tanks->findByFuelType((int) $fuelTypeId);
            $this->tankService->deductSales($tank, $liters);
        }
    }

    /**
     * @param  Collection<int, MeterReading>  $readings
     */
    private function assertCreditLitersDoNotExceedSales(Shift $shift, $readings): void
    {
        $soldByFuel = [];

        foreach ($readings as $reading) {
            $fuelTypeId = $reading->nozzle->fuel_type_id;
            $soldByFuel[$fuelTypeId] = Decimal::add($soldByFuel[$fuelTypeId] ?? '0', $reading->litersSold());
        }

        $creditByFuel = $shift->customerTransactions()
            ->where('type', CustomerTransactionType::Sale)
            ->whereNotNull('fuel_type_id')
            ->get()
            ->groupBy('fuel_type_id');

        foreach ($creditByFuel as $fuelTypeId => $rows) {
            $credited = Decimal::of((string) $rows->sum('liters'));
            $sold = $soldByFuel[$fuelTypeId] ?? '0.000';

            if (Decimal::compare($credited, $sold) > 0) {
                throw new BusinessException(
                    "Credit liters ({$credited} L) exceed meter-derived sales ({$sold} L) for fuel type {$fuelTypeId}.",
                );
            }
        }
    }
}
