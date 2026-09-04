<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nozzles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pump_id')->constrained()->restrictOnDelete();
            $table->string('side', 1);
            $table->foreignId('fuel_type_id')->constrained()->restrictOnDelete();
            $table->decimal('last_closing_reading', 12, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['pump_id', 'side']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nozzles');
    }
};
