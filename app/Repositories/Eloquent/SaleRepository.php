<?php

namespace App\Repositories\Eloquent;

use App\Models\FuelType;
use App\Models\Nozzle;
use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SaleRepository extends BaseRepository implements SaleRepositoryInterface
{
    public function __construct(Sale $model)
    {
        parent::__construct($model);
    }

    public function totalsBetween(Carbon $from, Carbon $to): array
    {
        $totals = $this->query()
            ->whereHas('shift', function ($query) use ($from, $to): void {
                $query->whereBetween('start_time', [$from, $to]);
            })
            ->selectRaw('COALESCE(SUM(total_amount), 0) as amount')
            ->selectRaw('COALESCE(SUM(liters_sold), 0) as liters')
            ->selectRaw('COALESCE(SUM(total_cost), 0) as cost')
            ->selectRaw('COALESCE(SUM(profit), 0) as profit')
            ->first();

        return [
            'amount' => Decimal::money((string) ($totals->amount ?? 0)),
            'liters' => Decimal::of((string) ($totals->liters ?? 0)),
            'cost' => Decimal::money((string) ($totals->cost ?? 0)),
            'profit' => Decimal::money((string) ($totals->profit ?? 0)),
        ];
    }

    public function combinedByFuelTypeBetween(Carbon $from, Carbon $to): array
    {
        $sales = $this->salesInRange($from, $to);

        return FuelType::query()
            ->orderBy('name')
            ->get()
            ->map(function (FuelType $type) use ($sales): array {
                $rows = $sales->where('fuel_type_id', $type->id);

                return [
                    'fuel_type_id' => $type->id,
                    'fuel_type' => $type->name,
                    'code' => $type->code instanceof \BackedEnum ? $type->code->value : (string) $type->code,
                    'liters_sold' => Decimal::of((string) $rows->sum('liters_sold')),
                    'total_amount' => Decimal::money((string) $rows->sum('total_amount')),
                    'profit' => Decimal::money((string) $rows->sum('profit')),
                ];
            })
            ->values()
            ->all();
    }

    public function isolatedByNozzleBetween(Carbon $from, Carbon $to): array
    {
        $sales = $this->salesInRange($from, $to)->groupBy('nozzle_id');

        return Nozzle::query()
            ->with(['pump', 'fuelType'])
            ->where('is_active', true)
            ->orderBy('pump_id')
            ->orderBy('side')
            ->get()
            ->map(function (Nozzle $nozzle) use ($sales): array {
                $rows = $sales->get($nozzle->id) ?? collect();
                $side = $nozzle->side instanceof \BackedEnum ? $nozzle->side->value : (string) $nozzle->side;

                return [
                    'nozzle_id' => $nozzle->id,
                    'label' => $nozzle->label(),
                    'pump' => $nozzle->pump?->name,
                    'side' => $side,
                    'fuel_type_id' => $nozzle->fuel_type_id,
                    'fuel_type' => $nozzle->fuelType?->name,
                    'liters_sold' => Decimal::of((string) $rows->sum('liters_sold')),
                    'total_amount' => Decimal::money((string) $rows->sum('total_amount')),
                    'profit' => Decimal::money((string) $rows->sum('profit')),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Sale>
     */
    private function salesInRange(Carbon $from, Carbon $to)
    {
        return $this->query()
            ->with(['fuelType', 'nozzle.pump', 'nozzle.fuelType'])
            ->whereHas('shift', function ($query) use ($from, $to): void {
                $query->whereBetween('start_time', [$from, $to]);
            })
            ->get();
    }
}
