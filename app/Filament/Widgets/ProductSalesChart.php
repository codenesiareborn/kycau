<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProductSalesChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Produk Terlaris';

    protected int | string | array $columnSpan = 'half';

    protected function getData(): array
    {
        $productSales = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
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
