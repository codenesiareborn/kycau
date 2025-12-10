<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin users
        $admin1 = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin1->assignRole('admin');

        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@example.com'],
            [
                'name' => 'Admin Two',
                'password' => Hash::make('password'),
            ]
        );
        $admin2->assignRole('admin');

        // Create regular users
        $user1 = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
            ]
        );
        $user1->assignRole('user');

        $user2 = User::firstOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'User Two',
                'password' => Hash::make('password'),
            ]
        );
        $user2->assignRole('user');

        $this->command->info('Users created successfully!');
        $this->command->info('Admins:');
        $this->command->info('- admin@example.com (password)');
        $this->command->info('- admin2@example.com (password)');
        $this->command->info('Users:');
        $this->command->info('- user@example.com (password)');
        $this->command->info('- user2@example.com (password)');
    }
}
