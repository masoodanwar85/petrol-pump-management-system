<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FuelTypeResource;
use App\Repositories\Contracts\FuelTypeRepositoryInterface;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class FuelTypeController extends Controller
{
    public function __construct(
        private readonly FuelTypeRepositoryInterface $fuelTypes,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(
            FuelTypeResource::collection($this->fuelTypes->active()),
        );
    }
}
