<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UserRole;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role system
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default(UserRole::USER->value)->after('email');
            }
            
            // Contact information
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'county')) {
                $table->string('county')->nullable()->after('phone');
            }
            
            // Donor-specific fields
            if (!Schema::hasColumn('users', 'organization')) {
                $table->string('organization')->nullable()->after('county');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('organization');
            }
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('address');
            }
            
            // Preferences
            if (!Schema::hasColumn('users', 'newsletter_subscribed')) {
                $table->boolean('newsletter_subscribed')->default(false)->after('profile_picture');
            }
            if (!Schema::hasColumn('users', 'email_notifications')) {
                $table->boolean('email_notifications')->default(true)->after('newsletter_subscribed');
            }
            if (!Schema::hasColumn('users', 'sms_notifications')) {
                $table->boolean('sms_notifications')->default(false)->after('email_notifications');
            }
            
            // Donation preferences
            if (!Schema::hasColumn('users', 'preferred_causes')) {
                $table->json('preferred_causes')->nullable()->after('sms_notifications');
            }
            if (!Schema::hasColumn('users', 'preferred_payment_method')) {
                $table->string('preferred_payment_method')->nullable()->after('preferred_causes');
            }
            
            // Admin fields
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('preferred_payment_method');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('last_login_ip');
            }
            
            // Soft deletes for user data retention compliance
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'county',
                'organization',
                'address',
                'profile_picture',
                'newsletter_subscribed',
                'email_notifications',
                'sms_notifications',
                'preferred_causes',
                'preferred_payment_method',
                'last_login_at',
                'last_login_ip',
                'is_active',
                'deleted_at'
            ]);
        });
    }
};