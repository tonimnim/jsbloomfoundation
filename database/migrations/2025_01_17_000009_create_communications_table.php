<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('category')->default('general'); // general, partnership, media, etc.
            $table->string('status')->default('new'); // new, read, replied, archived
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->json('attachments')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['status', 'created_at']);
            $table->index(['category', 'status']);
            $table->index(['assigned_to']);
            $table->index(['priority', 'status']);
        });

        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subject');
            $table->longText('content');
            $table->string('status')->default('draft'); // draft, scheduled, sending, sent
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['status', 'scheduled_at']);
            $table->index(['created_by']);
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->json('preferences')->nullable(); // which topics they want
            $table->boolean('is_active')->default(true);
            $table->string('subscription_source')->nullable(); // website, donation, event
            $table->timestamp('subscribed_at');
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_token')->unique();
            $table->timestamps();
            
            $table->index(['is_active', 'subscribed_at']);
            $table->index(['email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('newsletters');
        Schema::dropIfExists('contact_messages');
    }
};