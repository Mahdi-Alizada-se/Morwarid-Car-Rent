<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'customer'])->default('customer')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->string('avatar', 255)->nullable()->after('phone');
            $table->enum('locale', ['en', 'fa'])->default('en')->after('avatar');
            $table->string('social_provider', 50)->nullable()->after('locale');
            $table->string('social_id', 255)->nullable()->after('social_provider');
            $table->string('fcm_token', 255)->nullable()->after('social_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'avatar', 'locale', 'social_provider', 'social_id', 'fcm_token']);
        });
    }
};