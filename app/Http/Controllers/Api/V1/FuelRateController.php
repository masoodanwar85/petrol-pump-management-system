<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FuelRate\StoreFuelRateRequest;
use App\Http\Resources\Api\V1\FuelRateResource;
use App\Repositories\Contracts\FuelRateRepositoryInterface;
use App\Services\FuelRateService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class FuelRateController extends Controller
{
    public function __construct(
        private readonly FuelRateRepositoryInterface $rates,
        private readonly FuelRateService $service,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::paginated(
            $this->rates->paginate(perPage: 20, with: ['fuelType']),
            FuelRateResource::class,
        );
    }

    public function store(StoreFuelRateRequest $request): JsonResponse
    {
        $rate = $this->service->create($request->validated())->load('fuelType');

        return ApiResponse::created(new FuelRateResource($rate), 'Fuel rate created.');
    }
}
