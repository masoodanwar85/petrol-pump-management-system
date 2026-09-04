<?php

namespace App\Services;

use App\Enums\TankTransactionType;
use App\Exceptions\BusinessException;
use App\Models\Tank;
use App\Models\TankTransaction;
use App\Repositories\Contracts\TankRepositoryInterface;
use App\Repositories\Contracts\TankTransactionRepositoryInterface;
use App\Support\Decimal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TankService
{
    public function __construct(
        private readonly TankRepositoryInterface $tanks,
        private readonly TankTransactionRepositoryInterface $transactions,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordTransaction(Tank $tank, array $data): TankTransaction
    {
        $type = TankTransactionType::from($data['type']);
        $quantity = Decimal::of($data['quantity_liters']);

        if ($type !== TankTransactionType::Adjustment && Decimal::compare($quantity, '0') <= 0) {
            throw new BusinessException('Quantity must be greater than zero.');
        }

        if ($type === TankTransactionType::Adjustment && Decimal::isZero($quantity)) {
            throw new BusinessException('Adjustment quantity cannot be zero.');
        }

        $costPerLiter = null;
        $totalCost = null;

        if ($type === TankTransactionType::Purchase) {
            if (! isset($data['cost_per_liter'])) {
                throw new BusinessException('cost_per_liter is required for tanker purchases.');
            }

            $costPerLiter = Decimal::money($data['cost_per_liter']);
            $totalCost = Decimal::money(Decimal::multiply($quantity, $costPerLiter, 4));
        }

        return DB::transaction(function () use ($tank, $type, $quantity, $costPerLiter, $totalCost, $data): TankTransaction {
            $locked = Tank::query()->lockForUpdate()->findOrFail($tank->id);

            $signedQuantity = $this->signedQuantity($type, $quantity);
            $nextStock = Decimal::add($locked->current_stock, $signedQuantity);

            if (Decimal::isNegative($nextStock)) {
                throw new BusinessException('This transaction would make tank stock negative.');
            }

            if (Decimal::compare($nextStock, $locked->capacity) > 0) {
                throw new BusinessException('This transaction would exceed tank capacity.');
            }

            $avgCost = $locked->weighted_avg_cost;

            if ($type === TankTransactionType::Purchase) {
                $avgCost = $this->recalculateWeightedAverage(
                    $locked->current_stock,
                    $locked->weighted_avg_cost,
                    $quantity,
                    $costPerLiter,
                );
            }

            $transaction = $this->transactions->create([
                'tank_id' => $locked->id,
                'type' => $type,
                'quantity_liters' => $quantity,
                'cost_per_liter' => $costPerLiter,
                'total_cost' => $totalCost,
                'date' => $data['date'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->tanks->update($locked, [
                'current_stock' => $nextStock,
                'weighted_avg_cost' => $avgCost,
            ]);

            return $transaction->load('tank.fuelType');
        });
    }

    public function deductSales(Tank $tank, string $liters): void
    {
        DB::transaction(function () use ($tank, $liters): void {
            $locked = Tank::query()->lockForUpdate()->findOrFail($tank->id);
            $nextStock = Decimal::subtract($locked->current_stock, $liters);

            if (Decimal::isNegative($nextStock)) {
                throw new BusinessException(
                    "Insufficient {$locked->name} stock to close this shift. Sold {$liters} L but only {$locked->current_stock} L is available.",
                );
            }

            $this->tanks->update($locked, [
                'current_stock' => $nextStock,
            ]);
        });
    }

    private function signedQuantity(TankTransactionType $type, string $quantity): string
    {
        return match ($type) {
            TankTransactionType::Purchase => $quantity,
            TankTransactionType::Wastage => Decimal::multiply($quantity, '-1'),
            TankTransactionType::Adjustment => $quantity,
        };
    }

    private function recalculateWeightedAverage(
        string $currentStock,
        string $currentAvg,
        string $purchaseQty,
        string $purchaseCost,
    ): string {
        $existingValue = Decimal::multiply($currentStock, $currentAvg, 4);
        $incomingValue = Decimal::multiply($purchaseQty, $purchaseCost, 4);
        $newQty = Decimal::add($currentStock, $purchaseQty);

        if (Decimal::isZero($newQty)) {
            return '0.00';
        }

        return Decimal::money(
            Decimal::divide(Decimal::add($existingValue, $incomingValue, 4), $newQty, 6),
        );
    }
}
