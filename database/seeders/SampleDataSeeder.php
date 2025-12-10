<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Laravolt\Indonesia\Models\City;
use Carbon\Carbon;
use Faker\Factory as Faker;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get the first user (superadmin) to assign sample data
        $user = User::first();

        if (!$user) {
            $this->command->error('No users found. Please run UserSeeders first.');
            return;
        }

        // Create sample products
        $products = [
            ['name' => 'Laptop', 'description' => 'Laptop untuk kebutuhan kerja dan gaming', 'price' => 15000000, 'user_id' => $user->id],
            ['name' => 'Smartphone', 'description' => 'Smartphone Android terbaru', 'price' => 6000000, 'user_id' => $user->id],
            ['name' => 'Tablet', 'description' => 'Tablet untuk produktivitas', 'price' => 8000000, 'user_id' => $user->id],
            ['name' => 'Monitor', 'description' => 'Monitor 24 inch Full HD', 'price' => 3000000, 'user_id' => $user->id],
            ['name' => 'Keyboard', 'description' => 'Keyboard mechanical gaming', 'price' => 1500000, 'user_id' => $user->id],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Get major cities
        $cities = City::whereIn('name', [
            'JAKARTA PUSAT',
            'JAKARTA SELATAN',
            'JAKARTA UTARA',
            'SURABAYA',
            'BANDUNG',
            'YOGYAKARTA',
            'MEDAN',
            'SEMARANG',
            'MAKASSAR',
            'MALANG',
            'PALEMBANG'
        ])->get();

        if ($cities->count() === 0) {
            // Fallback: get any cities
            $cities = City::take(10)->get();
        }

        // Create sample customers
        $customers = [];
        for ($i = 0; $i < 50; $i++) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'name' => $faker->name,
                'phone' => $faker->phoneNumber,
                'email' => $faker->unique()->email,
                'address' => $faker->address,
                'city_id' => $cities->random()->id,
            ]);
            $customers[] = $customer;
        }

        // Create sample sales data for the last 6 months
        $products = Product::all();
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($i = 0; $i < 200; $i++) {
            $saleDate = Carbon::now()->subDays(rand(0, 180));
            $customer = collect($customers)->random();

            $sale = Sale::create([
                'user_id' => $user->id,
                'month' => $months[$saleDate->month - 1],
                'sale_date' => $saleDate,
                'sale_number' => 'INV-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'total_amount' => 0,
            ]);

            $itemsCount = rand(1, 4);
            $saleTotal = 0;

            for ($itemIndex = 0; $itemIndex < $itemsCount; $itemIndex++) {
                $product = $products->random();
                $quantity = rand(1, 5);
                $lineTotal = $product->price * $quantity * (1 + (rand(-20, 30) / 100));

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);

                $saleTotal += $lineTotal;
            }

            $sale->update(['total_amount' => $saleTotal]);
        }
    }
}
