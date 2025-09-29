<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CitySalesChart extends ChartWidget
{
    protected ?string $heading = 'Kota Terbanyak Pembelian';

    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $citySales = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('indonesia_cities', 'customers.city_id', '=', 'indonesia_cities.id')
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
