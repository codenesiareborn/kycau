<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Imports\SalesImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importSales')
                ->label('Import Sales')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->storeFiles(false)
                        ->helperText('Unggah file XLSX penjualan yang akan diimpor.'),
                    TextInput::make('year')
                        ->label('Tahun')
                        ->required()
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(now()->year + 1)
                        ->default((string) now()->year)
                        ->helperText('Tahun akan digabung dengan kolom bulan dan tanggal dari file.'),
                ])
                ->action(function (array $data): void {
                    $file = $data['file'] ?? null;
                    $year = isset($data['year']) ? (int) $data['year'] : null;

                    if (! $file instanceof TemporaryUploadedFile) {
                        Notification::make()
                            ->title('Tidak ada file')
                            ->body('Silakan pilih file XLSX terlebih dahulu.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($year === null || $year < 1900) {
                        Notification::make()
                            ->title('Tahun tidak valid')
                            ->body('Masukkan tahun dengan format empat digit.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        Excel::import(new SalesImport($year), $file->getRealPath());

                        Notification::make()
                            ->title('Import berhasil')
                            ->body("Data penjualan tahun {$year} berhasil diimpor.")
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('Import gagal')
                            ->body('Terjadi kesalahan ketika memproses file.')
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
