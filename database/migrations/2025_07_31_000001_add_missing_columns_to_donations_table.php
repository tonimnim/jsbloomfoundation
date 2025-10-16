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
        Schema::table('donations', function (Blueprint $table) {
            // Add frequency column if it doesn't exist
            if (!Schema::hasColumn('donations', 'frequency')) {
                $table->string('frequency')->default('one-time')->after('payment_method'); // one-time, monthly
            }
            
            // Add project_designation column if it doesn't exist
            if (!Schema::hasColumn('donations', 'project_designation')) {
                $table->string('project_designation')->nullable()->after('project_id');
            }
            
            // Add is_anonymous column if it doesn't exist
            if (!Schema::hasColumn('donations', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false)->after('donor_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'project_designation', 'is_anonymous']);
        });
    }
};