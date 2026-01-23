<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('generations', function (Blueprint $table) {
            $table->string('original_thumb_sm')->nullable()->after('original_image_url');
            $table->string('original_thumb_md')->nullable()->after('original_thumb_sm');
            $table->string('processed_thumb_sm')->nullable()->after('processed_image_url');
            $table->string('processed_thumb_md')->nullable()->after('processed_thumb_sm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generations', function (Blueprint $table) {
            $table->dropColumn([
                'original_thumb_sm', 
                'original_thumb_md', 
                'processed_thumb_sm', 
                'processed_thumb_md'
            ]);
        });
    }
};
