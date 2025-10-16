<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Admin users to create
        $admins = [
            [
                'email' => 'admin@jsbloomfoundation.org',
                'name' => 'JS Bloom Admin',
            ],
        ];

        foreach ($admins as $admin) {
            // Check if user doesn't exist before creating
            if (!DB::table('users')->where('email', $admin['email'])->exists()) {
                DB::table('users')->insert([
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'password' => Hash::make('Playball22!'),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove the admin user
        DB::table('users')->where('email', 'admin@jsbloomfoundation.org')->delete();
    }
};