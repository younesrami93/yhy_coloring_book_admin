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
        Schema::create('app_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Null for guests
            $table->string('email')->nullable()->unique();
            $table->string('avatar_url')->nullable();

            // Social Login Fields
            $table->string('social_id')->nullable()->index(); // Google/FB ID
            $table->string('social_provider')->nullable(); // 'google', 'facebook'

            // Account Status
            $table->boolean('is_guest')->default(true);
            $table->integer('credits')->default(3); // Free starter credits

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
