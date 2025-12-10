<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penjualan')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('user_id')
                            ->label('Pemilik')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn() => auth()->id())
                            ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                            ->helperText('Pilih user yang akan memiliki penjualan ini'),
                        TextInput::make('sale_number')
                            ->label('Nomor Penjualan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: INV-001'),
                        TextInput::make('month')
                            ->label('Bulan')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('Contoh: 2025-01'),
                        DatePicker::make('sale_date')
                            ->label('Tanggal Penjualan')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Select::make('customer_id')
                            ->label('Pelanggan')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama Pelanggan')
                                    ->required(),
                                TextInput::make('phone')
                                    ->label('No. Telepon'),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                            ]),
                    ])
                    ->columns(3),

                Section::make('Item Penjualan')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(1),
                                TextInput::make('line_total')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->default(0)
                                    ->helperText('Harga x Jumlah')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(
                                fn(array $state): ?string =>
                                $state['product_id']
                                ? Product::find($state['product_id'])?->name
                                : null
                            ),
                    ]),

                Section::make('Total')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Total Penjualan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(0)
                            ->helperText('Total akan dihitung otomatis saat menyimpan'),
                    ]),
            ]);
    }
}
