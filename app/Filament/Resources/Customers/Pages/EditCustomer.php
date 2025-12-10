<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Manually inject all map data from Livewire state
        $data['latitude'] = $this->data['latitude'] ?? null;
        $data['longitude'] = $this->data['longitude'] ?? null;
        $data['address'] = $this->data['address'] ?? null;
        $data['city_id'] = $this->data['city_id'] ?? null;
        
        // Debug: Log what data is being saved
        Log::info('Customer form before save data:', [
            'form_data' => $data,
            'livewire_data' => $this->data,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'address' => $data['address'],
            'city_id' => $data['city_id']
        ]);
        
        return $data;
    }
}
