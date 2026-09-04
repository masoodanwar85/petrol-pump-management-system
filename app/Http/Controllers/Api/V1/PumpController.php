<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NozzleResource;
use App\Http\Resources\Api\V1\PumpResource;
use App\Repositories\Contracts\NozzleRepositoryInterface;
use App\Repositories\Contracts\PumpRepositoryInterface;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PumpController extends Controller
{
    public function __construct(
        private readonly PumpRepositoryInterface $pumps,
        private readonly NozzleRepositoryInterface $nozzles,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(
            PumpResource::collection($this->pumps->all(['nozzles.fuelType'])),
        );
    }

    public function nozzles(): JsonResponse
    {
        return ApiResponse::success(
            NozzleResource::collection($this->nozzles->all(['pump', 'fuelType'])),
        );
    }
}
