<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class SalesDataTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Data Penjualan';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('month')
                    ->label('BLN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sale_date')
                    ->label('TGL')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('sale_number')
                    ->label('NO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('NAMA')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.phone')
                    ->label('NO. HP')
                    ->searchable(),

                TextColumn::make('customer.email')
                    ->label('EMAIL')
                    ->searchable(),

                TextColumn::make('customer.city.name')
                    ->label('KOTA')
                    ->getStateUsing(fn ($record) =>
                        $record->customer?->city?->name
                            ? str_replace(['KOTA ', 'KABUPATEN '], '', $record->customer->city->name)
                            : '-'
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_summary')
                    ->label('PRODUK')
                    ->getStateUsing(fn ($record) => $record->items
                        ->loadMissing('product')
                        ->map(function ($item) {
                            return $item->product?->name;
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->join(', ')
                    ),

                TextColumn::make('total_quantity')
                    ->label('QTY')
                    ->getStateUsing(fn ($record) => $record->items->sum('quantity'))
                    ->alignCenter(),

                TextColumn::make('total_amount')
                    ->label('TOTAL PEMBELIAN')
                    ->numeric()
                    ->formatStateUsing(fn ($state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->alignEnd(),
            ])
            ->defaultSort('sale_date', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }

    protected function getTableQuery(): Builder
    {
        $filters = $this->pageFilters;

        $query = Sale::with(['items.product', 'customer.city']);

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

        return $query;
    }
}