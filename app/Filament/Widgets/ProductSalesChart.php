<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ProductSalesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Produk Terlaris';

    protected int|string|array $columnSpan = 'half';

    protected function getData(): array
    {
        $filters = $this->pageFilters;

        $query = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id');

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('sales.sale_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('sales.sale_date', '<=', $filters['date_to']);
        }

        // Apply product filter
        if (!empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }

        // Apply city filter
        if (!empty($filters['city_id'])) {
            $query->join('customers', 'sales.customer_id', '=', 'customers.id')
                ->where('customers.city_id', $filters['city_id']);
        }

        // Apply user filter (admin only)
        if (!empty($filters['user_id']) && auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            $query->where('sales.user_id', $filters['user_id']);
        }

        $productSales = $query
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_quantity'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Terjual',
                    'data' => $productSales->pluck('total_quantity')->toArray(),
                    'backgroundColor' => [
                        '#134e4a',
                        '#059669',
                        '#10b981',
                        '#34d399',
                        '#6ee7b7'
                    ],
                ],
            ],
            'labels' => $productSales->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
