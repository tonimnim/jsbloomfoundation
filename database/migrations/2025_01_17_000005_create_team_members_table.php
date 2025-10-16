<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Leadership, Board, CPCs, Staff
            $table->string('slug');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('county')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->json('social_links')->nullable(); // LinkedIn, Twitter, etc.
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('category_id')->constrained('team_categories')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Link to user account if they have one
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['category_id', 'is_active', 'sort_order']);
            $table->index(['county']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('team_categories');
    }
};