<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Contracts\View\View;

class CustomerMap extends Field
{
    protected string $view = 'filament.forms.components.customer-map';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getLatitude(): ?float
    {
        return $this->getLivewire()?->getRecord()?->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->getLivewire()?->getRecord()?->longitude;
    }
}
