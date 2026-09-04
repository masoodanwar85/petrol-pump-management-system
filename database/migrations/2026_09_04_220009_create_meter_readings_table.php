<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->foreignId('nozzle_id')->constrained()->restrictOnDelete();
            $table->decimal('opening_reading', 12, 3);
            $table->decimal('closing_reading', 12, 3)->nullable();
            $table->dateTime('opening_recorded_at');
            $table->dateTime('closing_recorded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['shift_id', 'nozzle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
