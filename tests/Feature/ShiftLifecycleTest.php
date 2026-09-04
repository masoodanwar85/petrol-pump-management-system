<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Models\Customer;
use App\Models\FuelRate;
use App\Models\FuelType;
use App\Models\Nozzle;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\Tank;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_returns_a_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@pump.test',
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'email']]]);
    }

    public function test_shift_start_end_derives_sales_and_deducts_tank_stock(): void
    {
        $user = User::query()->where('email', 'attendant@pump.test')->firstOrFail();
        $petrolTank = Tank::query()->where('name', 'Petrol Tank')->firstOrFail();
        $openingStock = $petrolTank->current_stock;

        $readings = Nozzle::query()->orderBy('id')->get()->map(fn (Nozzle $nozzle) => [
            'nozzle_id' => $nozzle->id,
            'opening_reading' => 10000,
        ])->all();

        $start = $this->actingAs($user, 'sanctum')->postJson('/api/v1/shifts/start', [
            'readings' => $readings,
        ]);

        $start->assertCreated()->assertJsonPath('data.status', 'open');
        $shiftId = $start->json('data.id');

        $closings = Nozzle::query()->orderBy('id')->get()->map(function (Nozzle $nozzle) {
            $sold = $nozzle->fuelType->code->value === 'petrol' ? 50 : 20;

            return [
                'nozzle_id' => $nozzle->id,
                'closing_reading' => 10000 + $sold,
            ];
        })->all();

        $end = $this->actingAs($user, 'sanctum')->postJson("/api/v1/shifts/{$shiftId}/end", [
            'readings' => $closings,
        ]);

        $end->assertOk()->assertJsonPath('data.status', 'closed');

        $this->assertSame(6, Sale::query()->count());
        $this->assertSame(ShiftStatus::Closed, Shift::query()->find($shiftId)?->status);

        $petrolSold = Sale::query()
            ->whereHas('fuelType', fn ($query) => $query->where('code', 'petrol'))
            ->sum('liters_sold');

        $this->assertSame('150.000', number_format((float) $petrolSold, 3, '.', ''));
        $this->assertSame(
            '11850.000',
            number_format((float) $petrolTank->fresh()->current_stock, 3, '.', ''),
        );
        $this->assertNotSame($openingStock, $petrolTank->fresh()->current_stock);
    }

    public function test_closed_shift_meter_readings_cannot_be_changed(): void
    {
        $user = User::query()->where('email', 'attendant@pump.test')->firstOrFail();
        $nozzles = Nozzle::query()->orderBy('id')->get();

        $start = $this->actingAs($user, 'sanctum')->postJson('/api/v1/shifts/start', [
            'readings' => $nozzles->map(fn (Nozzle $nozzle) => [
                'nozzle_id' => $nozzle->id,
                'opening_reading' => 20000,
            ])->all(),
        ]);

        $shiftId = $start->json('data.id');

        $this->actingAs($user, 'sanctum')->postJson("/api/v1/shifts/{$shiftId}/end", [
            'readings' => $nozzles->map(fn (Nozzle $nozzle) => [
                'nozzle_id' => $nozzle->id,
                'closing_reading' => 20010,
            ])->all(),
        ])->assertOk();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/meter-readings/closing', [
            'shift_id' => $shiftId,
            'nozzle_id' => $nozzles->first()->id,
            'closing_reading' => 20020,
        ])->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_fuel_rates_cannot_overlap(): void
    {
        $user = User::query()->where('email', 'admin@pump.test')->firstOrFail();
        $petrol = FuelType::query()->where('code', 'petrol')->firstOrFail();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/fuel-rates', [
            'fuel_type_id' => $petrol->id,
            'rate' => 280,
            'effective_from' => now()->subDays(2)->toDateTimeString(),
            'effective_to' => now()->addDay()->toDateTimeString(),
        ])->assertStatus(422);
    }

    public function test_credit_sale_respects_credit_limit(): void
    {
        $user = User::query()->where('email', 'attendant@pump.test')->firstOrFail();
        $customer = Customer::query()->firstOrFail();
        $customer->update(['credit_limit' => '100.00']);
        $petrol = FuelType::query()->where('code', 'petrol')->firstOrFail();
        $nozzles = Nozzle::query()->orderBy('id')->get();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/shifts/start', [
            'readings' => $nozzles->map(fn (Nozzle $nozzle) => [
                'nozzle_id' => $nozzle->id,
                'opening_reading' => 30000,
            ])->all(),
        ])->assertCreated();

        $this->actingAs($user, 'sanctum')->postJson("/api/v1/customers/{$customer->id}/credit-sales", [
            'fuel_type_id' => $petrol->id,
            'liters' => 20,
        ])->assertStatus(422);
    }

    public function test_dashboard_returns_required_keys(): void
    {
        $user = User::query()->where('email', 'admin@pump.test')->firstOrFail();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'today_sales_amount',
                    'today_liters',
                    'tank_levels',
                    'credit_outstanding',
                    'profit_today',
                    'alerts',
                ],
            ]);
    }

    public function test_new_open_ended_rate_closes_the_previous_one(): void
    {
        $user = User::query()->where('email', 'admin@pump.test')->firstOrFail();
        $petrol = FuelType::query()->where('code', 'petrol')->firstOrFail();
        $previous = FuelRate::query()->where('fuel_type_id', $petrol->id)->whereNull('effective_to')->firstOrFail();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/fuel-rates', [
            'fuel_type_id' => $petrol->id,
            'rate' => 275.25,
            'effective_from' => now()->addHour()->toDateTimeString(),
        ])->assertCreated();

        $this->assertNotNull($previous->fresh()->effective_to);
        $this->assertSame(1, FuelRate::query()->where('fuel_type_id', $petrol->id)->whereNull('effective_to')->count());
    }
}
