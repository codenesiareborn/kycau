<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pemilik')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn() => auth()->id())
                    ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                    ->helperText('Pilih user yang akan memiliki produk ini'),
                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Masukkan nama produk'),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(4)
                    ->placeholder('Masukkan deskripsi produk (opsional)')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->placeholder('0')
                    ->helperText('Harga per unit produk'),
            ]);
    }
}
