<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tank\StoreTankTransactionRequest;
use App\Http\Requests\Api\V1\Tank\UpdateTankRequest;
use App\Http\Resources\Api\V1\TankResource;
use App\Http\Resources\Api\V1\TankTransactionResource;
use App\Models\Tank;
use App\Repositories\Contracts\TankRepositoryInterface;
use App\Repositories\Contracts\TankTransactionRepositoryInterface;
use App\Services\TankService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TankController extends Controller
{
    public function __construct(
        private readonly TankRepositoryInterface $tanks,
        private readonly TankTransactionRepositoryInterface $transactions,
        private readonly TankService $service,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(
            TankResource::collection($this->tanks->all(['fuelType'])),
        );
    }

    public function show(Tank $tank): JsonResponse
    {
        return ApiResponse::success(new TankResource($tank->load('fuelType')));
    }

    public function update(UpdateTankRequest $request, Tank $tank): JsonResponse
    {
        $tank = $this->tanks->update($tank, $request->validated());

        return ApiResponse::success(new TankResource($tank->load('fuelType')), 'Tank updated.');
    }

    public function transactions(Tank $tank): JsonResponse
    {
        return ApiResponse::paginated(
            $this->transactions->paginateForTank($tank->id),
            TankTransactionResource::class,
        );
    }

    public function storeTransaction(StoreTankTransactionRequest $request, Tank $tank): JsonResponse
    {
        $transaction = $this->service->recordTransaction($tank, $request->validated());

        return ApiResponse::created(new TankTransactionResource($transaction), 'Tank transaction recorded.');
    }
}
