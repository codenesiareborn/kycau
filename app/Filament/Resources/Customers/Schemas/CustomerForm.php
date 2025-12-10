<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Filament\Forms\Components\CustomerMap;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('phone')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(50)
                    ->nullable(),
                TextInput::make('latitude')
                    ->label('Latitude')
                    ->hidden()
                    ->numeric()
                    ->step(0.00000001)
                    ->default(null),
                TextInput::make('longitude')
                    ->label('Longitude')
                    ->hidden()
                    ->numeric()
                    ->step(0.00000001)
                    ->default(null),
                TextInput::make('city_id')
                    ->label('City ID')
                    ->hidden()
                    ->numeric()
                    ->default(null),
                CustomerMap::make('map')
                    ->label('🗺️ Lokasi Customer - Tentukan alamat dan kota dengan menyeret marker atau klik pada peta')
                    ->columnSpanFull(),
            ]);
    }
}
