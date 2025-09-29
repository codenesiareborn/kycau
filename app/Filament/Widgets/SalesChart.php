<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Total Penjualan per Bulan';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $monthsData = [];
        $salesData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthsData[] = $month->format('M Y');

            $monthlySales = Sale::whereMonth('sale_date', $month->month)
                ->whereYear('sale_date', $month->year)
                ->sum('total_amount');

            $salesData[] = $monthlySales / 1000000; // Convert to millions
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan (Juta Rupiah)',
                    'data' => $salesData,
                    'borderColor' => '#134e4a',
                    'backgroundColor' => 'rgba(19, 78, 74, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $monthsData,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
