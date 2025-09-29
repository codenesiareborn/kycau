<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('month')
                    ->required(),
                DatePicker::make('sale_date')
                    ->required(),
                TextInput::make('sale_number')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
            ]);
    }
}
