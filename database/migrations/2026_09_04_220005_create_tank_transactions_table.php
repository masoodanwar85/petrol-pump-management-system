<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tank_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('quantity_liters', 12, 3);
            $table->decimal('cost_per_liter', 10, 2)->nullable();
            $table->decimal('total_cost', 14, 2)->nullable();
            $table->date('date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tank_id', 'date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tank_transactions');
    }
};
