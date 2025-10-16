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
        Schema::table('gallery_images', function (Blueprint $table) {
            // Drop unnecessary columns
            $table->dropColumn(['title', 'description', 'category', 'event_date', 'sort_order', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            // Re-add columns if rolling back
            $table->string('title')->after('id');
            $table->text('description')->nullable()->after('title');
            $table->string('category')->nullable()->after('mime_type');
            $table->date('event_date')->nullable()->after('category');
            $table->integer('sort_order')->default(0)->after('event_date');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });
    }
};
