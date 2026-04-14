<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Regular users
        $users = [
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['name' => 'Bob Johnson', 'email' => 'bob@example.com'],
            ['name' => 'Alice Brown', 'email' => 'alice@example.com'],
            ['name' => 'Charlie Wilson', 'email' => 'charlie@example.com'],
        ];

        foreach ($users as $userData) {
            User::create([
                ...$userData,
                'password' => Hash::make('password'),
                'role' => 'user',
            ]);
        }

        // Banned user (soft ban)
        User::create([
            'name' => 'Spammer Account',
            'email' => 'spammer@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'ban_type' => 'soft',
            'banned_at' => now(),
            'ban_expires_at' => now()->addDays(7),
            'ban_reason' => 'Multiple spam reports',
        ]);

        // Affiliate user
        User::create([
            'name' => 'Affiliate Partner',
            'email' => 'affiliate@example.com',
            'password' => Hash::make('password'),
            'role' => 'affiliate',
        ]);
    }
}
