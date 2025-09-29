<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('month')
                    ->searchable(),
                TextColumn::make('sale_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('sale_number')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('items_summary')
                    ->label('Products')
                    ->getStateUsing(fn ($record) => $record->items
                        ->loadMissing('product')
                        ->map(function ($item) {
                            $productName = $item->product?->name;

                            if (! $productName) {
                                return null;
                            }

                            return sprintf('%s (Qty: %d)', $productName, max(1, (int) $item->quantity));
                        })
                        ->filter()
                        ->values()
                        ->join(', '))
                    ->toggleable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
