<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CitySalesChart;
use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\ProductSalesChart;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\SalesDataTable;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard Data';

    protected static ?string $navigationLabel = 'Dashboard Data';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getWidgets(): array
    {
        return [
            DashboardOverview::class,
            SalesChart::class,
            ProductSalesChart::class,
            CitySalesChart::class,
            SalesDataTable::class,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Data')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Tanggal Mulai')
                            ->placeholder('mm/dd/yyyy')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('date_to')
                            ->label('Tanggal Akhir')
                            ->placeholder('mm/dd/yyyy')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Select::make('product_id')
                            ->label('Produk')
                            ->placeholder('Semua Produk')
                            ->options(Product::pluck('name', 'id'))
                            ->searchable(),

                        Select::make('city_id')
                            ->label('Kota')
                            ->placeholder('Semua Kota')
                            ->options(function () {
                                return DB::table('indonesia_cities')
                                    ->whereIn('id', function ($query) {
                                        $query->select('city_id')
                                            ->from('customers')
                                            ->whereNotNull('city_id');
                                    })
                                    ->pluck('name', 'id')
                                    ->map(function ($name) {
                                        return str_replace(['KOTA ', 'KABUPATEN '], '', $name);
                                    });
                            })
                            ->searchable(),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
            ]);
    }
}
