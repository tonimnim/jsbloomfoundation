<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('donation_number')->unique(); // Auto-generated reference
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('payment_method'); // mpesa, paypal, card, bank_transfer
            $table->string('status')->default('pending'); // pending, processing, completed, failed, refunded
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Anonymous donations allowed
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null'); // General fund if null
            
            // Donor information (for anonymous donations)
            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->string('donor_phone')->nullable();
            $table->string('donor_organization')->nullable();
            $table->text('donor_message')->nullable();
            
            // Payment gateway details
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable(); // Store full response
            $table->string('receipt_number')->nullable(); // For M-Pesa receipts
            
            // Recurring donation details
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable(); // monthly, quarterly, yearly
            $table->foreignId('parent_donation_id')->nullable()->constrained('donations')->onDelete('set null');
            $table->date('next_payment_date')->nullable();
            $table->boolean('recurring_active')->default(true);
            
            // Tax receipt
            $table->boolean('tax_receipt_sent')->default(false);
            $table->timestamp('tax_receipt_sent_at')->nullable();
            
            // Timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Indexes for fast queries and reporting
            $table->index(['status', 'created_at']);
            $table->index(['payment_method', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['project_id', 'status']);
            $table->index(['is_recurring', 'next_payment_date']);
            $table->index(['donation_number']);
            $table->index(['created_at']); // For time-based reporting
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // payment, refund, chargeback
            $table->string('gateway'); // mpesa, paypal, stripe
            $table->string('gateway_transaction_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->string('status');
            $table->json('gateway_data'); // Full gateway response
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['donation_id']);
            $table->index(['gateway', 'gateway_transaction_id']);
            $table->index(['transaction_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('donations');
    }
};