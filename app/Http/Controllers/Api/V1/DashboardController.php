<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ShiftResource;
use App\Services\DashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function __invoke(): JsonResponse
    {
        $data = $this->dashboard->today();

        if ($data['open_shift']) {
            $data['open_shift'] = new ShiftResource($data['open_shift']);
        }

        return ApiResponse::success($data);
    }
}
