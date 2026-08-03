<?php

namespace App\Services\Reporting\Cache;

use Carbon\CarbonInterval;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Base cache implementation for reporting services.
 *
 * Responsibilities:
 * - Retrieve cached reporting data.
 * - Detect corrupted serialized cache payloads.
 * - Rebuild invalid cache entries automatically.
 * - Provide a common TTL contract.
 *
 * Concrete cache services are responsible for:
 * - Cache key generation.
 * - Calling the appropriate reporting service.
 */
abstract class BaseReportingCacheService
{
    protected function remember(
        string $key,
        Closure $callback,
    ): mixed {

        if (Cache::has($key)) {

            $cached = Cache::get($key);

            if (
                is_object($cached)
                && get_class($cached) === '__PHP_Incomplete_Class'
            ) {

                Cache::forget($key);

            } else {

                return $cached;

            }
        }

        $data = $callback();

        Cache::put(
            $key,
            $data,
            $this->ttl(),
        );

        return $data;
    }

    abstract protected function ttl(): CarbonInterval;
}