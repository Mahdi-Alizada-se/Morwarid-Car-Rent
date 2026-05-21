<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('tracker_token', 64)->nullable()->unique()->after('status');
            $table->timestamp('last_seen_at')->nullable()->after('tracker_token');
            $table->decimal('last_latitude', 10, 7)->nullable()->after('last_seen_at');
            $table->decimal('last_longitude', 10, 7)->nullable()->after('last_latitude');
            $table->decimal('last_speed', 5, 2)->nullable()->default(0)->after('last_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'tracker_token',
                'last_seen_at',
                'last_latitude',
                'last_longitude',
                'last_speed',
            ]);
        });
    }
};