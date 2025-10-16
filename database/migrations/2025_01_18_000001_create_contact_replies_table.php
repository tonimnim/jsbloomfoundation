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
        Schema::create('contact_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_submission_id')->constrained('contact_submissions')->onDelete('cascade');
            $table->foreignId('replied_by')->constrained('users');
            $table->text('reply_message');
            $table->string('reply_subject')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->json('metadata')->nullable(); // For storing email sending details
            $table->timestamps();
            
            $table->index('contact_submission_id');
            $table->index('replied_by');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_replies');
    }
};