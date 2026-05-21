<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'driver_license_image')) {
                $table->string('driver_license_image', 255)->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'driver_license_number')) {
                $table->string('driver_license_number', 100)->nullable()->after('driver_license_image');
            }
            if (!Schema::hasColumn('users', 'driver_license_verified')) {
                $table->boolean('driver_license_verified')->default(false)->after('driver_license_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'driver_license_image',
                'driver_license_number',
                'driver_license_verified',
            ]);
        });
    }
};