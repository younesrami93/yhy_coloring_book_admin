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
        Schema::create('styles', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., "Mandala", "Cartoon"
            $table->text('prompt'); // The actual AI instruction
            $table->string('thumbnail_url'); // Small preview
            $table->string('example_before_url'); // Original photo example
            $table->string('example_after_url'); // Result example
            $table->unsignedBigInteger('usage_count')->default(0); // Cache for stats
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // Soft Delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('styles');
    }
};
