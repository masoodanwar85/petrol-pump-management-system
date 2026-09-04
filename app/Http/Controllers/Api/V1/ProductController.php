<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\StoreProductSaleRequest;
use App\Http\Requests\Api\V1\Product\StoreProductStockRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ProductSaleResource;
use App\Http\Resources\Api\V1\ProductStockResource;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductService $service,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::paginated(
            $this->products->paginate(),
            ProductResource::class,
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->create($request->validated());

        return ApiResponse::created(new ProductResource($product), 'Product created.');
    }

    public function show(Product $product): JsonResponse
    {
        return ApiResponse::success(new ProductResource($product));
    }

    public function addStock(StoreProductStockRequest $request, Product $product): JsonResponse
    {
        $movement = $this->service->addStock($product, $request->validated());

        return ApiResponse::created(new ProductStockResource($movement), 'Stock recorded.');
    }

    public function sell(StoreProductSaleRequest $request, Product $product): JsonResponse
    {
        $sale = $this->service->sell($product, $request->validated());

        return ApiResponse::created(new ProductSaleResource($sale), 'Product sale recorded.');
    }
}
