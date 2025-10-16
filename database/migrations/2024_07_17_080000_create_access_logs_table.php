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
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('method', 10); // GET, POST, PUT, DELETE, etc.
            $table->text('url');
            $table->string('route_name')->nullable();
            $table->integer('status_code');
            $table->string('ip_address', 45); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->decimal('response_time', 8, 3)->nullable(); // in milliseconds
            $table->json('request_data')->nullable(); // POST/PUT data
            $table->json('headers')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['status_code', 'created_at']);
            $table->index(['method', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};