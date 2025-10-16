<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('page_title')->nullable();
            $table->string('referrer')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45);
            $table->string('country')->nullable();
            $table->string('device_type')->nullable(); // mobile, tablet, desktop
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // Indexes for fast analytics queries
            $table->index(['url', 'viewed_at']);
            $table->index(['viewed_at']);
            $table->index(['user_id']);
            $table->index(['country', 'viewed_at']);
        });

        Schema::create('donation_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('total_donations')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('new_donors')->default(0);
            $table->integer('recurring_donations')->default(0);
            $table->json('payment_methods')->nullable(); // breakdown by method
            $table->json('project_breakdown')->nullable(); // donations per project
            $table->timestamps();
            
            $table->unique(['date']);
            $table->index(['date']);
        });

        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // login, logout, donation, profile_update, etc.
            $table->string('description')->nullable();
            $table->json('metadata')->nullable(); // additional data
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes for user tracking and security
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
        Schema::dropIfExists('donation_analytics');
        Schema::dropIfExists('page_views');
    }
};