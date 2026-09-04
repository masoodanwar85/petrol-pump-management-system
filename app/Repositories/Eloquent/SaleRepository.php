<?php

namespace App\Repositories\Eloquent;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Support\Decimal;
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
}
