<?php

namespace App\Domain\Projects\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function canBeCompleted(): bool
    {
        return $this === self::Active;
    }

    public function canBeCancelled(): bool
    {
        return $this === self::Active;
    }

    public function canBeEdited(): bool
    {
        return $this === self::Active;
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }
}
