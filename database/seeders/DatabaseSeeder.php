<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravolt\Indonesia\Seeds\CitiesSeeder;
use Laravolt\Indonesia\Seeds\ProvincesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles and permissions first
        $this->call([
            ShieldSeeder::class,
        ]);

        // Create users with roles
        $this->call([
            SuperAdminSeeder::class,
            UserSeeder::class,
        ]);

        // Then seed location and sample data
        $this->call([
            ProvincesSeeder::class,
            CitiesSeeder::class,
            SampleDataSeeder::class,
        ]);
    }
}
