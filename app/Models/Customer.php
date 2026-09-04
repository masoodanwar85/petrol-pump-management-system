<?php

namespace App\Models;

use App\Enums\CustomerTransactionType;
use App\Models\Concerns\Auditable;
use App\Support\Decimal;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'phone', 'vehicle_number', 'credit_limit', 'is_active', 'notes'])]
class Customer extends Model
{
    use Auditable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CustomerTransaction::class);
    }

    public function outstandingBalance(): string
    {
        $sales = $this->transactions()
            ->where('type', CustomerTransactionType::Sale)
            ->sum('amount');

        $payments = $this->transactions()
            ->where('type', CustomerTransactionType::Payment)
            ->sum('amount');

        return Decimal::subtract((string) $sales, (string) $payments, 2);
    }
}
