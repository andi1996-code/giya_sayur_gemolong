<?php

namespace App\Filament\Widgets;

use App\Models\Setting;
use Filament\Widgets\Widget;

class SettingsOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.settings-overview-widget';
    protected static ?int $sort = -100;
    protected int | string | array $columnSpan = 'full';

    public function getSetting()
    {
        return Setting::first();
    }
}
