<?php

namespace App\Repositories\Contracts;

use App\Models\FuelType;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<FuelType>
 */
interface FuelTypeRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?FuelType;

    /**
     * @return Collection<int, FuelType>
     */
    public function active(): Collection;
}
