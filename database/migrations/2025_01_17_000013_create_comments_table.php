<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('commentable_type'); // App\Models\Post, App\Models\Project, etc.
            $table->unsignedBigInteger('commentable_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Anonymous comments allowed
            
            // Guest comment info (for anonymous comments)
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade'); // For nested comments
            $table->string('status')->default('pending'); // pending, approved, rejected, spam
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['commentable_type', 'commentable_id', 'status']);
            $table->index(['user_id']);
            $table->index(['parent_id']);
            $table->index(['status', 'created_at']);
            $table->index(['ip_address']); // For spam detection
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};