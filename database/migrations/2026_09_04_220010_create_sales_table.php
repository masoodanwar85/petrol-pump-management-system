<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->foreignId('nozzle_id')->constrained()->restrictOnDelete();
            $table->foreignId('fuel_type_id')->constrained()->restrictOnDelete();
            $table->decimal('liters_sold', 12, 3);
            $table->decimal('rate_per_liter', 10, 2);
            $table->decimal('total_amount', 14, 2);
            $table->decimal('cost_per_liter', 10, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->decimal('profit', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['shift_id', 'nozzle_id']);
            $table->index(['fuel_type_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
