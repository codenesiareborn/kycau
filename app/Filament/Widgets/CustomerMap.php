<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Sale;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class CustomerMap extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected string $view = 'filament.widgets.customer-map';

    protected int | string | array $columnSpan = 'full';

    public function getCustomerMapData(): array
    {
        $filters = $this->pageFilters;

        // Get top customers by total sales amount (limit to 150 customers max)
        $topCustomerIds = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('indonesia_cities', 'customers.city_id', '=', 'indonesia_cities.id')
            ->whereNotNull('customers.city_id')
            ->whereRaw("JSON_EXTRACT(indonesia_cities.meta, '$.lat') IS NOT NULL")
            ->whereRaw("JSON_EXTRACT(indonesia_cities.meta, '$.long') IS NOT NULL")
            ->when(!empty($filters['date_from']), fn($q) => $q->whereDate('sales.sale_date', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn($q) => $q->whereDate('sales.sale_date', '<=', $filters['date_to']))
            ->when(!empty($filters['city_id']), fn($q) => $q->where('customers.city_id', $filters['city_id']))
            ->select('sales.customer_id', DB::raw('SUM(sales.total_amount) as total_sales'))
            ->groupBy('sales.customer_id')
            ->orderBy('total_sales', 'desc')
            ->limit(200)
            ->pluck('customer_id');

        $query = Sale::with(['customer.city', 'items.product'])
            ->whereIn('customer_id', $topCustomerIds);

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('sale_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('sale_date', '<=', $filters['date_to']);
        }

        // Apply product filter
        if (!empty($filters['product_id'])) {
            $query->whereHas('items', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        $sales = $query->get();

        // Group sales by customer and aggregate data
        $customerData = [];
        foreach ($sales as $sale) {
            $customerId = $sale->customer_id;

            if (!isset($customerData[$customerId])) {
                $city = $sale->customer->city;
                $customerData[$customerId] = [
                    'name' => $sale->customer->name,
                    'address' => $sale->customer->address,
                    'city_name' => str_replace(['KOTA ', 'KABUPATEN '], '', $city->name ?? ''),
                    'lat' => (float) ($city->meta['lat'] ?? 0),
                    'lng' => (float) ($city->meta['long'] ?? 0),
                    'total_amount' => 0,
                    'products' => [],
                ];
            }

            $customerData[$customerId]['total_amount'] += $sale->total_amount;

            // Collect unique products
            foreach ($sale->items as $item) {
                $productName = $item->product->name;
                if (!in_array($productName, $customerData[$customerId]['products'])) {
                    $customerData[$customerId]['products'][] = $productName;
                }
            }
        }

        // Format the data for map display
        return array_values(array_map(function ($customer) {
            return [
                'name' => $customer['name'],
                'address' => $customer['address'],
                'city' => $customer['city_name'],
                'lat' => $customer['lat'],
                'lng' => $customer['lng'],
                'amount' => $customer['total_amount'],
                'products' => implode(', ', array_slice($customer['products'], 0, 3)) .
                             (count($customer['products']) > 3 ? '...' : ''),
            ];
        }, $customerData));
    }
}
