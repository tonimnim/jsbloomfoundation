<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureAdminExists extends Command
{
    protected $signature = 'admin:ensure';

    protected $description = 'Ensure an admin user exists, creating one from environment variables if not';

    public function handle(): int
    {
        // Check if any admin exists
        if (User::where('role', UserRole::ADMIN)->exists()) {
            $this->info('Admin user already exists. Skipping creation.');
            return Command::SUCCESS;
        }

        // Get credentials from environment
        $name = env('ADMIN_NAME', 'Admin');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (empty($email) || empty($password)) {
            $this->warn('No admin exists and ADMIN_EMAIL/ADMIN_PASSWORD not set in environment.');
            $this->warn('Set these environment variables to auto-create an admin on deploy.');
            return Command::SUCCESS;
        }

        // Create the admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->info("Admin user created: {$email}");

        return Command::SUCCESS;
    }
}
