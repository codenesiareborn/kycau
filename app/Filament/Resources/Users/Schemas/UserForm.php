<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
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
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn(string $context): bool => $context === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->maxLength(255)
                    ->helperText(
                        fn(string $context): ?string =>
                        $context === 'edit'
                        ? 'Kosongkan jika tidak ingin mengubah password'
                        : null
                    ),

                Select::make('role')
                    ->label('Role')
                    ->options(Role::pluck('name', 'name'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->afterStateHydrated(function (Select $component, $record) {
                        if ($record) {
                            $component->state($record->roles->first()?->name);
                        }
                    })
                    ->dehydrated(false)
                    ->helperText('Pilih role untuk user ini'),
            ]);
    }
}
