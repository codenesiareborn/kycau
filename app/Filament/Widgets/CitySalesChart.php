<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class CitySalesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Kota Terbanyak Pembelian';

    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $filters = $this->pageFilters;

        $query = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('indonesia_cities', 'customers.city_id', '=', 'indonesia_cities.id');

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('sales.sale_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('sales.sale_date', '<=', $filters['date_to']);
        }

        // Apply product filter
        if (!empty($filters['product_id'])) {
            $query->whereHas('items', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        // Apply city filter
        if (!empty($filters['city_id'])) {
            $query->where('customers.city_id', $filters['city_id']);
        }

        $citySales = $query
            ->select('indonesia_cities.name', DB::raw('COUNT(sales.id) as total_sales'))
            ->groupBy('indonesia_cities.id', 'indonesia_cities.name')
            ->orderBy('total_sales', 'desc')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $citySales->pluck('total_sales')->toArray(),
                    'backgroundColor' => [
                        '#134e4a',
                        '#059669',
                        '#10b981',
                        '#34d399',
                        '#6ee7b7'
                    ],
                ],
            ],
            'labels' => $citySales->pluck('name')->map(function($name) {
                return str_replace(['KOTA ', 'KABUPATEN '], '', $name);
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
