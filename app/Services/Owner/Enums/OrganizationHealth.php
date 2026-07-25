<?php

namespace App\Services\Owner\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;


/**
 * TODO (Phase 3)
 *
 * Extend health calculation using:
 * - Storage usage
 * - Failed payments
 * - Grace period
 * - Recent owner activity
 * - Support incidents
 
private function calculateHealth(): OrganizationHealth
{
    ...
}
**/
enum OrganizationHealth: string implements HasColor, HasIcon, HasLabel
{
    case HEALTHY = 'healthy';

    case WARNING = 'warning';

    case CRITICAL = 'critical';

    public function getLabel(): ?string
    {
        return match ($this) {

            self::HEALTHY => 'Healthy',

            self::WARNING => 'Needs Attention',

            self::CRITICAL => 'Critical',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {

            self::HEALTHY => 'success',

            self::WARNING => 'warning',

            self::CRITICAL => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {

            self::HEALTHY => 'heroicon-o-check-badge',

            self::WARNING => 'heroicon-o-exclamation-circle',

            self::CRITICAL => 'heroicon-o-x-circle',
        };
    }
}
