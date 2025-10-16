<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('beneficiary_name');
            $table->string('location'); // County/area
            $table->text('story');
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable(); // additional images
            $table->string('video_url')->nullable();
            $table->date('story_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['project_id', 'is_published']);
            $table->index(['is_featured', 'story_date']);
            $table->index(['location']);
            
            // Conditional fulltext index (only for MySQL/PostgreSQL)
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['title', 'story']); // Search optimization
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('success_stories');
    }
};