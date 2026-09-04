<?php

use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\FuelRateController;
use App\Http\Controllers\Api\V1\FuelTypeController;
use App\Http\Controllers\Api\V1\MeterReadingController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PumpController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\ShiftController;
use App\Http\Controllers\Api\V1\TankController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/fuel-types', [FuelTypeController::class, 'index']);

    Route::get('/fuel-rates', [FuelRateController::class, 'index']);
    Route::post('/fuel-rates', [FuelRateController::class, 'store']);

    Route::get('/tanks', [TankController::class, 'index']);
    Route::get('/tanks/{tank}', [TankController::class, 'show']);
    Route::put('/tanks/{tank}', [TankController::class, 'update']);
    Route::get('/tanks/{tank}/transactions', [TankController::class, 'transactions']);
    Route::post('/tanks/{tank}/transactions', [TankController::class, 'storeTransaction']);

    Route::get('/pumps', [PumpController::class, 'index']);
    Route::get('/nozzles', [PumpController::class, 'nozzles']);

    Route::get('/shifts', [ShiftController::class, 'index']);
    Route::get('/shifts/current', [ShiftController::class, 'current']);
    Route::post('/shifts/start', [ShiftController::class, 'start']);
    Route::get('/shifts/{shift}', [ShiftController::class, 'show']);
    Route::post('/shifts/{shift}/end', [ShiftController::class, 'end']);

    Route::get('/meter-readings', [MeterReadingController::class, 'index']);
    Route::post('/meter-readings/opening', [MeterReadingController::class, 'opening']);
    Route::post('/meter-readings/closing', [MeterReadingController::class, 'closing']);

    Route::get('/sales', [SaleController::class, 'index']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
    Route::get('/customers/{customer}/ledger', [CustomerController::class, 'ledger']);
    Route::post('/customers/{customer}/credit-sales', [CustomerController::class, 'creditSale']);
    Route::post('/customers/{customer}/payments', [CustomerController::class, 'payment']);

    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/products/{product}/stock', [ProductController::class, 'addStock']);
    Route::post('/products/{product}/sales', [ProductController::class, 'sell']);

    Route::get('/dashboard', DashboardController::class);
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/profit', [ReportController::class, 'profit']);
    Route::get('/alerts', AlertController::class);
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
});
