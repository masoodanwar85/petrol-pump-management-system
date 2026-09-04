<?php

namespace App\Services;

use App\Enums\CustomerTransactionType;
use App\Enums\ProductStockType;
use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\ProductSale;
use App\Models\ProductStock;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\CustomerTransactionRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductSaleRepositoryInterface;
use App\Repositories\Contracts\ProductStockRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductStockRepositoryInterface $stock,
        private readonly ProductSaleRepositoryInterface $sales,
        private readonly ShiftRepositoryInterface $shifts,
        private readonly CustomerTransactionRepositoryInterface $customerTransactions,
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $data['current_stock'] = $data['current_stock'] ?? '0';

        return $this->products->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addStock(Product $product, array $data): ProductStock
    {
        $type = ProductStockType::from($data['type']);
        $quantity = Decimal::of($data['quantity']);

        if ($type === ProductStockType::Purchase && Decimal::compare($quantity, '0') <= 0) {
            throw new BusinessException('Purchase quantity must be greater than zero.');
        }

        if ($type === ProductStockType::Adjustment && Decimal::isZero($quantity)) {
            throw new BusinessException('Adjustment quantity cannot be zero.');
        }

        $unitCost = Decimal::money($data['unit_cost'] ?? $product->purchase_price);
        $totalCost = Decimal::money(Decimal::multiply($quantity, $unitCost, 4));

        return DB::transaction(function () use ($product, $type, $quantity, $unitCost, $totalCost, $data): ProductStock {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $nextStock = Decimal::add($locked->current_stock, $quantity);

            if (Decimal::isNegative($nextStock)) {
                throw new BusinessException('This adjustment would make product stock negative.');
            }

            $movement = $this->stock->create([
                'product_id' => $locked->id,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'date' => $data['date'] ?? Carbon::now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $updates = ['current_stock' => $nextStock];

            if ($type === ProductStockType::Purchase) {
                $updates['purchase_price'] = $unitCost;
            }

            $this->products->update($locked, $updates);

            return $movement->load('product');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sell(Product $product, array $data): ProductSale
    {
        $quantity = Decimal::of($data['quantity']);

        if (Decimal::compare($quantity, '0') <= 0) {
            throw new BusinessException('Sale quantity must be greater than zero.');
        }

        $unitPrice = Decimal::money($data['unit_price'] ?? $product->selling_price);
        $unitCost = Decimal::money($product->purchase_price);
        $totalAmount = Decimal::money(Decimal::multiply($quantity, $unitPrice, 4));
        $totalCost = Decimal::money(Decimal::multiply($quantity, $unitCost, 4));
        $profit = Decimal::subtract($totalAmount, $totalCost, 2);

        $shift = isset($data['shift_id'])
            ? $this->shifts->findOrFail((int) $data['shift_id'])
            : $this->shifts->currentOpen();

        if ($shift && $shift->isClosed()) {
            throw new BusinessException('Product sales cannot be recorded against a closed shift.');
        }

        return DB::transaction(function () use ($product, $data, $quantity, $unitPrice, $unitCost, $totalAmount, $totalCost, $profit, $shift): ProductSale {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $nextStock = Decimal::subtract($locked->current_stock, $quantity);

            if (Decimal::isNegative($nextStock)) {
                throw new BusinessException("Insufficient stock for {$locked->name}. Available: {$locked->current_stock}.");
            }

            if (! empty($data['customer_id'])) {
                $customer = $this->customers->findOrFail((int) $data['customer_id']);
                $nextOutstanding = Decimal::add($customer->outstandingBalance(), $totalAmount, 2);

                if (Decimal::compare($nextOutstanding, $customer->credit_limit, 2) > 0) {
                    throw new BusinessException(
                        "This product sale would exceed the customer's credit limit of {$customer->credit_limit}.",
                    );
                }

                $this->customerTransactions->create([
                    'customer_id' => $customer->id,
                    'type' => CustomerTransactionType::Sale,
                    'amount' => $totalAmount,
                    'date' => $data['date'] ?? Carbon::now()->toDateString(),
                    'shift_id' => $shift?->id,
                    'notes' => 'Product sale: '.$locked->name,
                    'created_by' => Auth::id(),
                ]);
            }

            $sale = $this->sales->create([
                'product_id' => $locked->id,
                'shift_id' => $shift?->id,
                'customer_id' => $data['customer_id'] ?? null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'profit' => $profit,
                'date' => $data['date'] ?? Carbon::now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->products->update($locked, ['current_stock' => $nextStock]);

            return $sale->load(['product', 'customer', 'shift']);
        });
    }
}
