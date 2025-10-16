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
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('filename'); // Unique filename
            $table->string('path'); // Path in storage
            $table->string('url'); // Public URL
            $table->bigInteger('size')->nullable(); // File size in bytes
            $table->string('mime_type')->nullable(); // MIME type
            $table->string('category')->nullable(); // e.g., 'events', 'programs', 'community'
            $table->date('event_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};
