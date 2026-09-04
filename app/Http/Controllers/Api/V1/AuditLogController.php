<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $logs,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::paginated(
            $this->logs->paginate(perPage: 30, with: ['user']),
            AuditLogResource::class,
        );
    }
}
