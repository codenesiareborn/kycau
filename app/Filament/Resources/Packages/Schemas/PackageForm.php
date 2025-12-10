<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Paket')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Identifier unik untuk paket (contoh: trial, basic, pro)'),

                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Harga & Durasi')
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required()
                            ->helperText('Masukkan 0 untuk paket gratis'),

                        TextInput::make('duration_days')
                            ->label('Durasi (hari)')
                            ->numeric()
                            ->nullable()
                            ->helperText('Kosongkan untuk paket Lifetime (tidak ada batas waktu)'),

                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Urutan tampil di halaman registrasi'),
                    ])
                    ->columns(3),

                Section::make('Fitur')
                    ->schema([
                        TagsInput::make('features')
                            ->label('Daftar Fitur')
                            ->placeholder('Tambah fitur...')
                            ->helperText('Tekan Enter untuk menambah fitur baru')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Paket non-aktif tidak akan tampil di halaman registrasi'),

                        Toggle::make('is_trial')
                            ->label('Paket Trial')
                            ->default(false)
                            ->helperText('Tandai jika ini adalah paket trial/percobaan'),
                    ])
                    ->columns(2),
            ]);
    }
}
