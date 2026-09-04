<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductSale;
use App\Repositories\Contracts\ProductSaleRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Carbon;

class ProductSaleRepository extends BaseRepository implements ProductSaleRepositoryInterface
{
    public function __construct(ProductSale $model)
    {
        parent::__construct($model);
    }

    public function totalsBetween(Carbon $from, Carbon $to): array
    {
        $totals = $this->query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as amount')
            ->selectRaw('COALESCE(SUM(total_cost), 0) as cost')
            ->selectRaw('COALESCE(SUM(profit), 0) as profit')
            ->first();

        return [
            'amount' => Decimal::money((string) ($totals->amount ?? 0)),
            'cost' => Decimal::money((string) ($totals->cost ?? 0)),
            'profit' => Decimal::money((string) ($totals->profit ?? 0)),
        ];
    }
}
