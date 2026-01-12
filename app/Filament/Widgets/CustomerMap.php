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

    protected int|string|array $columnSpan = 'full';

    public function getCustomerMapData(): array
    {
        $filters = $this->pageFilters;

        // Get top customers by total sales amount (limit to 500 customers max)
        $topCustomerIds = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('indonesia_cities', 'customers.city_id', '=', 'indonesia_cities.id')
            ->whereNotNull('customers.city_id')
            ->when(!empty($filters['date_from']), fn($q) => $q->whereDate('sales.sale_date', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn($q) => $q->whereDate('sales.sale_date', '<=', $filters['date_to']))
            ->when(!empty($filters['city_id']), fn($q) => $q->where('customers.city_id', $filters['city_id']))
            ->when(!empty($filters['user_id']) && auth()->user()?->hasAnyRole(['admin', 'super_admin']), fn($q) => $q->where('sales.user_id', $filters['user_id']))
            ->select('sales.customer_id', DB::raw('SUM(sales.total_amount) as total_sales'))
            ->groupBy('sales.customer_id')
            ->orderBy('total_sales', 'desc')
            ->limit(10000)
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

        // Apply user filter (admin only)
        if (!empty($filters['user_id']) && auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $sales = $query->get();

        // Group sales by customer and collect all sales records
        $customerData = [];
        foreach ($sales as $sale) {
            $customerId = $sale->customer_id;

            if (!isset($customerData[$customerId])) {
                $city = $sale->customer->city;
                $customerData[$customerId] = [
                    'name' => $sale->customer->name,
                    'address' => $sale->customer->address,
                    'city_name' => str_replace(['KOTA ', 'KABUPATEN '], '', $city->name ?? ''),
                    'lat' => (float) ($sale->customer->latitude ?? $city?->meta['lat'] ?? 0),
                    'lng' => (float) ($sale->customer->longitude ?? $city?->meta['long'] ?? 0),
                    'total_amount' => 0,
                    'sales_count' => 0,
                    'sales_records' => [],
                ];
            }

            $customerData[$customerId]['total_amount'] += $sale->total_amount;
            $customerData[$customerId]['sales_count']++;

            // Add individual sales record
            $products = [];
            foreach ($sale->items as $item) {
                $products[] = $item->product->name;
            }

            $customerData[$customerId]['sales_records'][] = [
                'sale_id' => $sale->id,
                'amount' => $sale->total_amount,
                'date' => $sale->sale_date,
                'products' => implode(', ', $products),
            ];
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
                'sales_count' => $customer['sales_count'],
                'sales_records' => $customer['sales_records'],
            ];
        }, $customerData));
    }
}
