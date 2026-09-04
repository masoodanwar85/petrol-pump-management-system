<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductStock;
use App\Repositories\Contracts\ProductStockRepositoryInterface;

class ProductStockRepository extends BaseRepository implements ProductStockRepositoryInterface
{
    public function __construct(ProductStock $model)
    {
        parent::__construct($model);
    }
}
