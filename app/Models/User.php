<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'newsletter_subscribed' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'is_active' => 'boolean',
            'preferred_causes' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->role)) {
                $user->role = UserRole::USER;
            }
        });
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->role->permissions());
    }

    /**
     * Get user's donations relationship.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get user's total donated amount.
     */
    public function getTotalDonatedAttribute(): float
    {
        return $this->donations()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get user's last donation.
     */
    public function getLastDonationAttribute()
    {
        return $this->donations()->latest()->first();
    }

    /**
     * Check if user is subscribed to newsletter.
     */
    public function isNewsletterSubscribed(): bool
    {
        return $this->newsletter_subscribed;
    }

    /**
     * Get user's preferred causes as comma-separated string.
     */
    public function getPreferredCausesStringAttribute(): string
    {
        return is_array($this->preferred_causes) 
            ? implode(', ', $this->preferred_causes) 
            : '';
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only admins.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', UserRole::ADMIN);
    }

    /**
     * Scope to get only regular users.
     */
    public function scopeUsers($query)
    {
        return $query->where('role', UserRole::USER);
    }

    /**
     * Scope to get newsletter subscribers.
     */
    public function scopeNewsletterSubscribers($query)
    {
        return $query->where('newsletter_subscribed', true);
    }

    /**
     * Log user activity.
     */
    public function logActivity(string $action, ?string $description = null, array $metadata = []): void
    {
        \DB::table('user_activity_logs')->insert([
            'user_id' => $this->id,
            'action' => $action,
            'description' => $description,
            'metadata' => json_encode($metadata),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
