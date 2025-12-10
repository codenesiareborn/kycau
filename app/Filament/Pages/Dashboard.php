<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CitySalesChart;
use App\Filament\Widgets\CustomerMap;
use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\ProductSalesChart;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\SalesDataTable;
use App\Filament\Widgets\UploadHistoryTable;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\View\View;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Support\Enums\Width;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    protected $listeners = ['refresh' => '$refresh'];

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
            CustomerMap::class,
            SalesDataTable::class,
            UploadHistoryTable::class,
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

                        Select::make('user_id')
                            ->label('User')
                            ->placeholder('Semua User')
                            ->options(\App\Models\User::pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                            ->helperText('Filter data berdasarkan user'),

                        ViewField::make('clear_button')
                            ->view('filament.forms.components.clear-filters-button')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'date_from' => null,
            'date_to' => null,
            'product_id' => null,
            'city_id' => null,
            'user_id' => null,
        ];
        $this->dispatch('refresh');
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.dashboard-header');
    }

    public function getFooter(): ?View
    {
        return view('filament.pages.dashboard-footer');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        // Admins always have access
        if ($user?->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        // Users must have an active package
        return $user?->hasActivePackage() ?? false;
    }
}

