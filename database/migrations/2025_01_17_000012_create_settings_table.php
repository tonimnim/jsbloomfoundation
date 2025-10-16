<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, json, file
            $table->string('group')->default('general'); // general, contact, social, payment, etc.
            $table->text('description')->nullable();
            $table->boolean('is_editable')->default(true);
            $table->timestamps();
            
            $table->index(['group']);
            $table->index(['key']);
        });

        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, success, warning, error
            $table->string('category'); // donation, application, system, etc.
            $table->json('metadata')->nullable(); // additional data
            $table->boolean('is_read')->default(false);
            $table->boolean('is_global')->default(false); // show to all admins
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // specific user
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Indexes for fast notification queries
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['is_global', 'is_read', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index(['expires_at']);
        });

        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // database, files, full
            $table->string('status'); // started, completed, failed
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'status']);
            $table->index(['started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('site_settings');
    }
};