<?php

namespace App\Domain\Billing;


enum SubscriptionStatus: string
{
    case ACTIVE = 'active';

    case TRIAL = 'trial';

    case PAST_DUE = 'past_due';

    case CANCELLED = 'cancelled';

    case EXPIRED = 'expired';



    public function isTrial(): bool
    {
        return $this === self::TRIAL;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isPastDue(): bool
    {
        return $this === self::PAST_DUE;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this === self::EXPIRED;
    }

    /**
     * Human-friendly label for display.
     *
     * "past_due" is intentionally presented as "Grace Period" because
     * that is how the business interprets this state.
     */
    public function label(): string
    {
        return match ($this) {
            self::PAST_DUE => 'Grace Period',
            default => ucfirst($this->value),
        };
    }

    /**
     * Filament badge color for this status.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::TRIAL => 'info',
            self::PAST_DUE => 'warning',
            self::CANCELLED => 'gray',
            self::EXPIRED => 'danger',
        };
    }
}
