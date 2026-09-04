<?php

namespace App\Repositories\Contracts;

use App\Models\Nozzle;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<Nozzle>
 */
interface NozzleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return Collection<int, Nozzle>
     */
    public function active(): Collection;
}
