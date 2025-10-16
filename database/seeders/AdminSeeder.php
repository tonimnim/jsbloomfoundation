<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin users to create
        $admins = [
            [
                'email' => 'admin@jsbloomfoundation.org',
                'name' => 'JS Bloom Admin',
            ],
        ];

        // Create admin users with the same password
        foreach ($admins as $adminData) {
            $user = User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => Hash::make('Playball22!'),
                    'role' => UserRole::ADMIN,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );
            
            $this->command->info('Admin user created: ' . $adminData['email']);
        }
        
        $this->command->info('All admin users have been created with the password: Playball22!');
    }
}