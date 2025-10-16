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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // donation, application, contact, system, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the notification
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Null for system-wide notifications
            $table->foreignId('related_id')->nullable(); // Related entity ID (donation_id, application_id, etc.)
            $table->string('related_type')->nullable(); // Related entity type
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable(); // URL to view the related item
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'created_at']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};