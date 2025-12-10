<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Trial',
                'slug' => 'trial',
                'price' => 0,
                'duration_days' => 14,
                'is_active' => true,
                'is_trial' => true,
                'sort_order' => 1,
                'description' => 'Paket percobaan gratis selama 14 hari untuk mencoba semua fitur.',
                'features' => [
                    'Akses semua fitur',
                    'Maksimal 100 produk',
                    'Maksimal 50 pelanggan',
                    'Laporan dasar',
                ],
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price' => 99000,
                'duration_days' => 30,
                'is_active' => false, // Disabled for now
                'is_trial' => false,
                'sort_order' => 2,
                'description' => 'Paket dasar untuk bisnis kecil dengan fitur standar.',
                'features' => [
                    'Akses semua fitur',
                    'Unlimited produk',
                    'Unlimited pelanggan',
                    'Laporan lengkap',
                    'Export Excel',
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 199000,
                'duration_days' => 30,
                'is_active' => false, // Disabled for now
                'is_trial' => false,
                'sort_order' => 3,
                'description' => 'Paket profesional dengan fitur lengkap untuk bisnis menengah.',
                'features' => [
                    'Semua fitur Basic',
                    'Multi-user access',
                    'Dashboard analytics',
                    'Priority support',
                    'API access',
                ],
            ],
            [
                'name' => 'Lifetime',
                'slug' => 'lifetime',
                'price' => 999000,
                'duration_days' => null, // Lifetime = no expiration
                'is_active' => false, // Disabled for now
                'is_trial' => false,
                'sort_order' => 4,
                'description' => 'Paket selamanya dengan akses tanpa batas waktu.',
                'features' => [
                    'Semua fitur Pro',
                    'Akses selamanya',
                    'Update gratis selamanya',
                    'Priority support selamanya',
                    'White-label option',
                ],
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }
    }
}
