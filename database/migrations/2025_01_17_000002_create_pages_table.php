<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->json('meta_data')->nullable(); // SEO: title, description, keywords
            $table->string('status')->default('draft'); // draft, published, archived
            $table->string('template')->default('default'); // page template type
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['status', 'published_at']);
            $table->index(['slug']);
            $table->index(['is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};