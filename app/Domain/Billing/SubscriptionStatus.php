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
}
