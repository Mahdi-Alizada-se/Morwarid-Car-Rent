<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('invoice_path');
            $table->string('bank_reference')->nullable()->after('receipt_path');
            $table->string('rejection_reason')->nullable()->after('bank_reference');
            $table->foreignId('confirmed_by')->nullable()->after('rejection_reason')
                ->constrained('users')->nullOnDelete();
        });

        // Update status enum to include new values
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM(
            'pending',
            'receipt_uploaded',
            'paid',
            'rejected',
            'refunded',
            'failed'
        ) DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn([
                'receipt_path',
                'bank_reference',
                'rejection_reason',
            ]);
        });

        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM(
            'pending',
            'paid',
            'refunded',
            'failed'
        ) DEFAULT 'pending'");
    }
};