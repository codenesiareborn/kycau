<?php

namespace App\Filament\Widgets;

use App\Models\FileUpload;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\FileUpload as FileUploadComponent;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class UploadHistoryTable extends BaseWidget implements HasForms
{
    use InteractsWithForms;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Riwayat Upload File';

    protected function getTableActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload Data')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    FileUploadComponent::make('file')
                        ->label('File Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->maxSize(10240) // 10MB
                        ->required()
                        ->helperText('Unggah file XLSX penjualan (.xlsx, .xls - Maksimal 10MB)')
                        ->storeFiles(false),

                    TextInput::make('year')
                        ->label('Tahun')
                        ->required()
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(now()->year + 1)
                        ->default((string) now()->year)
                        ->helperText('Tahun akan digabung dengan kolom bulan dan tanggal dari file.'),
                ])
                ->action(function (array $data) {
                    $this->handleFileUpload($data['file'], (int) $data['year']);
                })
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('original_filename')
                    ->label('Nama File')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Upload')
                    ->formatStateUsing(function ($state) {
                        return $state->format('d M Y, H:i');
                    })
                    ->sortable(),

                TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(function (FileUpload $record) {
                        return $record->formatted_file_size;
                    }),

                TextColumn::make('records_processed')
                    ->label('Records')
                    ->formatStateUsing(function ($state) {
                        return number_format($state);
                    })
                    ->alignCenter(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function (FileUpload $record) {
                        return $record->status_badge;
                    })
                    ->colors([
                        'success' => fn(FileUpload $record): bool => $record->status === 'completed',
                        'warning' => fn(FileUpload $record): bool => $record->status === 'processing',
                        'danger' => fn(FileUpload $record): bool => $record->status === 'failed',
                        'gray' => fn(FileUpload $record): bool => $record->status === 'pending',
                    ]),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state ? (string) $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions($this->getTableActions())
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Hapus')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn(Collection $records) => $records->each->delete()),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }

    protected function getTableQuery(): Builder
    {
        $filters = $this->pageFilters;
        $query = FileUpload::query()->latest();

        // Apply user filter (admin only)
        if (!empty($filters['user_id']) && auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }

    protected function handleFileUpload($uploadedFile, int $year): void
    {
        try {
            // Validate file input
            if (!$uploadedFile instanceof TemporaryUploadedFile) {
                Notification::make()
                    ->title('Tidak ada file')
                    ->body('Silakan pilih file XLSX terlebih dahulu.')
                    ->danger()
                    ->send();
                return;
            }

            // Validate year
            if ($year < 1900) {
                Notification::make()
                    ->title('Tahun tidak valid')
                    ->body('Masukkan tahun dengan format empat digit.')
                    ->danger()
                    ->send();
                return;
            }

            // Get file information
            $originalName = $uploadedFile->getClientOriginalName();
            $fileSize = $uploadedFile->getSize();

            // Create file upload record
            $fileUpload = FileUpload::create([
                'user_id' => auth()->id(),
                'filename' => $uploadedFile->getRealPath(),
                'original_filename' => $originalName,
                'file_size' => $fileSize,
                'status' => 'processing',
            ]);

            // Process the file using SalesImport
            $this->processFileImport($uploadedFile->getRealPath(), $fileUpload, $year);

            Notification::make()
                ->title('Import berhasil')
                ->body("Data penjualan tahun {$year} berhasil diimpor.")
                ->success()
                ->send();

            // Refresh the page to update widgets
            $this->dispatch('refresh');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import gagal')
                ->body('Terjadi kesalahan ketika memproses file: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function processFileImport(string $filePath, FileUpload $fileUpload, int $year): void
    {
        try {
            $import = new \App\Imports\SalesImport($year);

            // Import using Excel facade directly from file path
            \Maatwebsite\Excel\Facades\Excel::import($import, $filePath);

            // Update status to completed
            $fileUpload->update([
                'status' => 'completed',
                'records_processed' => $this->countProcessedRecords(),
                'processed_at' => now(),
            ]);

        } catch (\Exception $e) {
            $fileUpload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processed_at' => now(),
            ]);

            throw $e; // Re-throw to be caught by handleFileUpload
        }
    }

    protected function countProcessedRecords(): int
    {
        // Count recent sales records as approximation
        return \App\Models\Sale::where('created_at', '>=', now()->subMinutes(5))->count();
    }
}