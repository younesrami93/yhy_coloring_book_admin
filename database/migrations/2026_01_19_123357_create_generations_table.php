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
        Schema::create('generations', function (Blueprint $table) {
            $table->id();
            // Foreign key to app_users (not the admin users table)
            $table->foreignId('user_id')->constrained('app_users')->onDelete('cascade');

            $table->string('original_image_url');
            $table->string('processed_image_url')->nullable(); // Null until AI finishes

            $table->string('style_name')->default('Classic');
            $table->text('prompt_used')->nullable();

            // Status tracking for the Job
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('cost_in_credits')->default(1);

            $table->softDeletes(); // As requested
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
