<?php

namespace App\Services\Owner\Infrastructure;

use Illuminate\Support\Facades\Cache;

class InfrastructureCacheService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected InfrastructureMetricsService $metrics
    )
    {
        
    }

    public function healthMetrics(): array
    {
        $serialized = Cache::remember(
            'owner.infrastructure.health',
            now()->addMinutes(5),
            fn () => serialize(
                $this->metrics->healthMetrics()
            )
        );

        return unserialize($serialized);
    }

        public function forget(): void
    {
        Cache::forget('owner.infrastructure.health');
    }

    public function refresh(): array
    {
        $this->forget();

        return $this->healthMetrics();
    }
}
