<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class SubscriptionExpiredWidget extends Widget
{
    protected string $view = 'filament.widgets.subscription-expired-widget';

    protected int|string|array $columnSpan = 'full';
}
