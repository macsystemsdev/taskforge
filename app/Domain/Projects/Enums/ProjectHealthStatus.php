<?php

namespace App\Domain\Projects\Enums;

enum ProjectHealthStatus: string
{
    case HEALTHY = 'healthy';

    case AT_RISK = 'at_risk';

    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::HEALTHY => 'Healthy',
            self::AT_RISK => 'At Risk',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::HEALTHY => 'success',
            self::AT_RISK => 'warning',
            self::CRITICAL => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::HEALTHY => 'heroicon-o-check-circle',
            self::AT_RISK => 'heroicon-o-exclamation-triangle',
            self::CRITICAL => 'heroicon-o-x-circle',
        };
    }
}
