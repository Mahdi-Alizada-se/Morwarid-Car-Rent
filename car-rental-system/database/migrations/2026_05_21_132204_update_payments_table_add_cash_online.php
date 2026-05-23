<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('bank_transfer','counter','cash','online') NOT NULL DEFAULT 'counter'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('bank_transfer','counter') NOT NULL DEFAULT 'counter'");
    }
};