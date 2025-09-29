<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Laravolt\Indonesia\Models\City;
use Carbon\Carbon;
use Faker\Factory as Faker;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Create sample products
        $products = [
            ['name' => 'Laptop', 'description' => 'Laptop untuk kebutuhan kerja dan gaming', 'price' => 15000000],
            ['name' => 'Smartphone', 'description' => 'Smartphone Android terbaru', 'price' => 6000000],
            ['name' => 'Tablet', 'description' => 'Tablet untuk produktivitas', 'price' => 8000000],
            ['name' => 'Monitor', 'description' => 'Monitor 24 inch Full HD', 'price' => 3000000],
            ['name' => 'Keyboard', 'description' => 'Keyboard mechanical gaming', 'price' => 1500000],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Get major cities
        $cities = City::whereIn('name', [
            'JAKARTA PUSAT', 'JAKARTA SELATAN', 'JAKARTA UTARA',
            'SURABAYA', 'BANDUNG', 'YOGYAKARTA', 'MEDAN',
            'SEMARANG', 'MAKASSAR', 'MALANG', 'PALEMBANG'
        ])->get();

        if ($cities->count() === 0) {
            // Fallback: get any cities
            $cities = City::take(10)->get();
        }

        // Create sample customers
        $customers = [];
        for ($i = 0; $i < 50; $i++) {
            $customer = Customer::create([
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
            $product = $products->random();
            $quantity = rand(1, 5);
            $totalAmount = $product->price * $quantity * (1 + (rand(-20, 30) / 100)); // Add some price variation

            Sale::create([
                'month' => $months[$saleDate->month - 1],
                'sale_date' => $saleDate,
                'sale_number' => 'INV-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'total_amount' => $totalAmount,
            ]);
        }
    }
}