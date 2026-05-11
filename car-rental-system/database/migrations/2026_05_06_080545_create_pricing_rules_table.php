<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->enum('type', ['hourly', 'daily', 'weekly', 'monthly']);
            $table->decimal('base_rate', 10, 2);
            $table->string('currency', 3)->default('AFN');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->decimal('multiplier', 4, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};