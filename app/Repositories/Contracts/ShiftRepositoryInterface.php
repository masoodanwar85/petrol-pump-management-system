<?php

namespace App\Repositories\Contracts;

use App\Models\Shift;

/**
 * @extends BaseRepositoryInterface<Shift>
 */
interface ShiftRepositoryInterface extends BaseRepositoryInterface
{
    public function currentOpen(): ?Shift;

    public function currentOpenForUser(int $userId): ?Shift;
}
