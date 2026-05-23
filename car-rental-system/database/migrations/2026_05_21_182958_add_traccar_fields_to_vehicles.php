<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'traccar_device_id')) {
                $table->string('traccar_device_id', 100)->nullable()->unique()->after('status');
            }
            if (!Schema::hasColumn('vehicles', 'traccar_device_name')) {
                $table->string('traccar_device_name', 100)->nullable()->after('traccar_device_id');
            }
            if (!Schema::hasColumn('vehicles', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('traccar_device_name');
            }
            if (!Schema::hasColumn('vehicles', 'last_latitude')) {
                $table->decimal('last_latitude', 10, 7)->nullable()->after('last_seen_at');
            }
            if (!Schema::hasColumn('vehicles', 'last_longitude')) {
                $table->decimal('last_longitude', 10, 7)->nullable()->after('last_latitude');
            }
            if (!Schema::hasColumn('vehicles', 'last_speed')) {
                $table->decimal('last_speed', 5, 2)->nullable()->default(0)->after('last_longitude');
            }
            if (!Schema::hasColumn('vehicles', 'last_address')) {
                $table->string('last_address', 255)->nullable()->after('last_speed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'traccar_device_id',
                'traccar_device_name',
                'last_address',
            ]);
        });
    }
};