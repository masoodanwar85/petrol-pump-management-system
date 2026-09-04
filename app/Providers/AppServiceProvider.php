<?php

namespace App\Providers;

use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\CustomerTransactionRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\FuelRateRepositoryInterface;
use App\Repositories\Contracts\FuelTypeRepositoryInterface;
use App\Repositories\Contracts\MeterReadingRepositoryInterface;
use App\Repositories\Contracts\NozzleRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductSaleRepositoryInterface;
use App\Repositories\Contracts\ProductStockRepositoryInterface;
use App\Repositories\Contracts\PumpRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\TankRepositoryInterface;
use App\Repositories\Contracts\TankTransactionRepositoryInterface;
use App\Repositories\Eloquent\AuditLogRepository;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Eloquent\CustomerTransactionRepository;
use App\Repositories\Eloquent\ExpenseRepository;
use App\Repositories\Eloquent\FuelRateRepository;
use App\Repositories\Eloquent\FuelTypeRepository;
use App\Repositories\Eloquent\MeterReadingRepository;
use App\Repositories\Eloquent\NozzleRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ProductSaleRepository;
use App\Repositories\Eloquent\ProductStockRepository;
use App\Repositories\Eloquent\PumpRepository;
use App\Repositories\Eloquent\SaleRepository;
use App\Repositories\Eloquent\ShiftRepository;
use App\Repositories\Eloquent\TankRepository;
use App\Repositories\Eloquent\TankTransactionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        FuelTypeRepositoryInterface::class => FuelTypeRepository::class,
        FuelRateRepositoryInterface::class => FuelRateRepository::class,
        TankRepositoryInterface::class => TankRepository::class,
        TankTransactionRepositoryInterface::class => TankTransactionRepository::class,
        PumpRepositoryInterface::class => PumpRepository::class,
        NozzleRepositoryInterface::class => NozzleRepository::class,
        ShiftRepositoryInterface::class => ShiftRepository::class,
        MeterReadingRepositoryInterface::class => MeterReadingRepository::class,
        SaleRepositoryInterface::class => SaleRepository::class,
        CustomerRepositoryInterface::class => CustomerRepository::class,
        CustomerTransactionRepositoryInterface::class => CustomerTransactionRepository::class,
        ExpenseRepositoryInterface::class => ExpenseRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        ProductStockRepositoryInterface::class => ProductStockRepository::class,
        ProductSaleRepositoryInterface::class => ProductSaleRepository::class,
        AuditLogRepositoryInterface::class => AuditLogRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
