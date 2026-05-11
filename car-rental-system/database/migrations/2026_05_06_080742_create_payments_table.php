<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('method', ['bank_transfer', 'counter']);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('AFN');
            $table->enum('status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->string('transaction_id')->unique()->nullable();
            $table->string('invoice_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};