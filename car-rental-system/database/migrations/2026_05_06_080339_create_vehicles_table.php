<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->foreignId('category_id')->constrained('vehicle_categories')->restrictOnDelete();
            $table->string('license_plate')->unique();
            $table->string('color');
            $table->integer('seats');
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid']);
            $table->enum('transmission', ['manual', 'automatic']);
            $table->enum('status', ['available', 'booked', 'maintenance'])->default('available');
            $table->integer('odometer')->default(0);
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};