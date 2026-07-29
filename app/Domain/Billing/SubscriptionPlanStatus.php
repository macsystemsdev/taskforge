<?php

namespace App\Domain\Billing;

enum SubscriptionPlanStatus: string
{
    case DRAFT = 'draft';

    case ACTIVE = 'active';

    case RETIRED = 'retired';

    case ARCHIVED = 'archived';

    /**
     * Determine if the plan is in draft.
     */
    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Determine if the plan is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Determine if the plan has been retired.
     */
    public function isRetired(): bool
    {
        return $this === self::RETIRED;
    }

    /**
     * Determine if the plan has been archived.
     */
    public function isArchived(): bool
    {
        return $this === self::ARCHIVED;
    }

    /**
     * Can new customers purchase this plan?
     */
    public function isPurchasable(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Should this plan be shown in pricing pages?
     */
    public function isVisible(): bool
    {
        return $this !== self::ARCHIVED;
    }

    /**
     * Can subscriptions on this plan continue renewing?
     *
     * Note:
     * A retired plan may still renew until its
     * retirement effective date. The renewal service
     * performs that final date check.
     */
    public function acceptsRenewals(): bool
    {
        return in_array($this, [
            self::ACTIVE,
            self::RETIRED,
        ], true);
    }
}
