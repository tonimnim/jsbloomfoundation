<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('newsletter_subscribers', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('unsubscribe_token');
            }
            
            if (!Schema::hasColumn('newsletter_subscribers', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};