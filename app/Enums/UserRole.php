<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::USER => 'User',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::ADMIN => [
                'manage_content',
                'manage_users',
                'manage_donations',
                'view_analytics',
                'manage_applications',
                'system_settings'
            ],
            self::USER => [
                'view_profile',
                'manage_donations',
                'download_receipts'
            ],
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}