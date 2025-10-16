<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6'); // hex color
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable(); // additional images
            $table->json('meta_data')->nullable(); // SEO data
            $table->string('status')->default('draft'); // draft, published, scheduled, archived
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->integer('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('category_id')->constrained('post_categories')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['author_id']);
            
            // Conditional fulltext index (only for MySQL/PostgreSQL)
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['title', 'excerpt', 'content']); // Search optimization
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');
    }
};