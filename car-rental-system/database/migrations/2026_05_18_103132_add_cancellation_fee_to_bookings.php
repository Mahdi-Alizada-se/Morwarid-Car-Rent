<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Only add columns that don't already exist
            if (!Schema::hasColumn('bookings', 'cancellation_fee')) {
                $table->decimal('cancellation_fee', 12, 2)->nullable()->default(null)->after('cancellation_reason');
            }
            if (!Schema::hasColumn('bookings', 'cancellation_fee_paid')) {
                $table->boolean('cancellation_fee_paid')->default(false)->after('cancellation_fee');
            }
            if (!Schema::hasColumn('bookings', 'booked_at')) {
                $table->timestamp('booked_at')->nullable()->after('cancellation_fee_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'cancellation_fee',
                'cancellation_fee_paid',
                'booked_at',
            ]);
        });
    }
};
