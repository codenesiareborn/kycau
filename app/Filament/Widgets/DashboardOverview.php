<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class DashboardOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $filters = $this->pageFilters;

        $query = Sale::query();

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

        // Apply city filter
        if (!empty($filters['city_id'])) {
            $query->whereHas('customer', function ($q) use ($filters) {
                $q->where('city_id', $filters['city_id']);
            });
        }

        $currentMonthSales = (clone $query)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');

        $previousMonthSales = (clone $query)
            ->whereMonth('sale_date', Carbon::now()->subMonth()->month)
            ->whereYear('sale_date', Carbon::now()->subMonth()->year)
            ->sum('total_amount');

        $monthlyGrowth = $previousMonthSales > 0
            ? (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100
            : 0;

        $yearSales = (clone $query)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');

        $previousYearSales = (clone $query)
            ->whereYear('sale_date', Carbon::now()->subYear()->year)
            ->sum('total_amount');

        $yearlyGrowth = $previousYearSales > 0
            ? (($yearSales - $previousYearSales) / $previousYearSales) * 100
            : 0;

        // Customer calculations with filters
        $customerQuery = Customer::query();
        if (!empty($filters['city_id'])) {
            $customerQuery->where('city_id', $filters['city_id']);
        }

        $avgTransactionPerCustomer = $customerQuery->withCount('sales')
            ->with(['sales' => function ($q) use ($filters) {
                if (!empty($filters['date_from'])) {
                    $q->whereDate('sale_date', '>=', $filters['date_from']);
                }
                if (!empty($filters['date_to'])) {
                    $q->whereDate('sale_date', '<=', $filters['date_to']);
                }
                if (!empty($filters['product_id'])) {
                    $q->whereHas('items', function ($query) use ($filters) {
                        $query->where('product_id', $filters['product_id']);
                    });
                }
            }])
            ->get()
            ->filter(fn($customer) => $customer->sales->count() > 0)
            ->map(fn($customer) => $customer->sales->sum('total_amount') / $customer->sales->count())
            ->avg();

        $avgUnitsPerCustomer = $customerQuery->with(['sales.items' => function ($q) use ($filters) {
                if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
                    $q->whereHas('sale', function ($query) use ($filters) {
                        if (!empty($filters['date_from'])) {
                            $query->whereDate('sale_date', '>=', $filters['date_from']);
                        }
                        if (!empty($filters['date_to'])) {
                            $query->whereDate('sale_date', '<=', $filters['date_to']);
                        }
                    });
                }
                if (!empty($filters['product_id'])) {
                    $q->where('product_id', $filters['product_id']);
                }
            }])
            ->get()
            ->filter(function ($customer) {
                return $customer->sales->flatMap->items->count() > 0;
            })
            ->map(function ($customer) {
                $totalUnits = $customer->sales->flatMap->items->sum('quantity');
                $salesCount = $customer->sales->count();
                return $salesCount > 0 ? $totalUnits / $salesCount : 0;
            })
            ->avg();

        return [
            Stat::make('Total Sales', 'Rp ' . number_format($currentMonthSales, 0, ',', '.'))
                ->description(($monthlyGrowth >= 0 ? '+' : '') . number_format($monthlyGrowth, 1) . '% dari bulan lalu')
                ->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyGrowth >= 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'stats-overview-widget-large',
                    'style' => 'font-size: 0.875rem !important; font-weight: 700 !important;'
                ]),

            Stat::make('This Year Sales', 'Rp ' . number_format($yearSales, 0, ',', '.'))
                ->description(($yearlyGrowth >= 0 ? '+' : '') . number_format($yearlyGrowth, 1) . '% dari tahun lalu')
                ->descriptionIcon($yearlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($yearlyGrowth >= 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'stats-overview-widget-large',
                    'style' => 'font-size: 0.875rem !important; font-weight: 700 !important;'
                ]),

            Stat::make('Avg Transaction/Customer', 'Rp ' . number_format($avgTransactionPerCustomer ?: 0, 0, ',', '.'))
                ->description('Avg trx per customer')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'stats-overview-widget-small',
                    'style' => 'font-size: 0.75rem !important; font-weight: 600 !important;'
                ]),

            Stat::make('Avg Units/Customer', number_format($avgUnitsPerCustomer ?: 0, 1))
                ->description('Avg unit per customer')
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'stats-overview-widget-small',
                    'style' => 'font-size: 0.75rem !important; font-weight: 600 !important;'
                ]),
        ];
    }
}
