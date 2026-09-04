<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AlertService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    public function __construct(
        private readonly AlertService $alerts,
    ) {}

    public function __invoke(): JsonResponse
    {
        return ApiResponse::success($this->alerts->active());
    }
}
