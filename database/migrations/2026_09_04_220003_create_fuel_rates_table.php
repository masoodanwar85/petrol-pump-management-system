<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_type_id')->constrained()->restrictOnDelete();
            $table->decimal('rate', 10, 2);
            $table->dateTime('effective_from');
            $table->dateTime('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fuel_type_id', 'effective_from', 'effective_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_rates');
    }
};
