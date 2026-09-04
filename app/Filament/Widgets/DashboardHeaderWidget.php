<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardHeaderWidget extends Widget
{
    protected  string $view = 'filament.widgets.dashboard-header';
    
    public static function canView(): bool
    {
        return true;
    }
    
    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }
}
