<?php

namespace App\Domain\Teams\Enums;


enum TeamProductivityStatus: string
{
    case HIGH = 'high';
    case NORMAL = 'normal';
    case LOW = 'low';
    case IDLE = 'idle';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {

            self::HIGH => 'High',

            self::NORMAL => 'Normal',

            self::LOW => 'Low',

            self::IDLE => 'Idle',

        };
    }

    /**
     * Filament badge color.
     */
    public function color(): string
    {
        return match ($this) {

            self::HIGH => 'success',

            self::NORMAL => 'primary',

            self::LOW => 'warning',

            self::IDLE => 'danger',

        };
    }
}