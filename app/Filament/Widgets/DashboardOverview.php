<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $currentMonthSales = Sale::whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');

        $previousMonthSales = Sale::whereMonth('sale_date', Carbon::now()->subMonth()->month)
            ->whereYear('sale_date', Carbon::now()->subMonth()->year)
            ->sum('total_amount');

        $monthlyGrowth = $previousMonthSales > 0
            ? (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100
            : 0;

        $yearSales = Sale::whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');

        $previousYearSales = Sale::whereYear('sale_date', Carbon::now()->subYear()->year)
            ->sum('total_amount');

        $yearlyGrowth = $previousYearSales > 0
            ? (($yearSales - $previousYearSales) / $previousYearSales) * 100
            : 0;

        $avgTransactionPerCustomer = Customer::withCount('sales')
            ->with('sales')
            ->get()
            ->filter(fn($customer) => $customer->sales_count > 0)
            ->map(fn($customer) => $customer->sales->sum('total_amount') / $customer->sales_count)
            ->avg();

        $avgUnitsPerCustomer = Customer::withCount('sales')
            ->with('sales')
            ->get()
            ->filter(fn($customer) => $customer->sales_count > 0)
            ->map(fn($customer) => $customer->sales->sum('quantity') / $customer->sales_count)
            ->avg();

        return [
            Stat::make('Total Sales', 'Rp ' . number_format($currentMonthSales, 0, ',', '.'))
                ->description(($monthlyGrowth >= 0 ? '+' : '') . number_format($monthlyGrowth, 1) . '% dari bulan lalu')
                ->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('This Year Sales', 'Rp ' . number_format($yearSales, 0, ',', '.'))
                ->description(($yearlyGrowth >= 0 ? '+' : '') . number_format($yearlyGrowth, 1) . '% dari tahun lalu')
                ->descriptionIcon($yearlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($yearlyGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Avg Transaction/Customer', 'Rp ' . number_format($avgTransactionPerCustomer ?: 0, 0, ',', '.'))
                ->description('Rata-rata transaksi per customer')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Avg Units/Customer', number_format($avgUnitsPerCustomer ?: 0, 1))
                ->description('Rata-rata unit per customer')
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
        ];
    }
}
