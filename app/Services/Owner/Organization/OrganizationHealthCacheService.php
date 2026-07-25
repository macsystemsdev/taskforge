<?php

namespace App\Services\Owner\Organization;

use Illuminate\Support\Facades\Cache;

class OrganizationHealthCacheService
{
    public function __construct(
        private OrganizationHealthService $health,
    ) {}

    public function overview()
    {
        $serialized = Cache::remember(
            'owner.dashboard.organization-health',
            now()->addMinutes(5),
            fn() => serialize(
                $this->health->organizations()
            )
        );

        return unserialize($serialized);
    }

    public function forget(): void
    {
        Cache::forget(
            'owner.dashboard.organization-health'
        );
    }

    public function refresh()
    {
        $this->forget();

        return $this->overview();
    }
}
