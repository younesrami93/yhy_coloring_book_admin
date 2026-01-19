<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_user_id')->constrained('app_users')->onDelete('cascade');

            $table->string('device_uuid')->unique(); // Unique Hardware ID from Flutter
            $table->string('fcm_token')->nullable(); // For Push Notifications
            $table->string('platform')->nullable(); // 'android' or 'ios'
            $table->string('language', 10)->default('en'); // 'en', 'fr', 'ar'
            $table->string('app_version')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
