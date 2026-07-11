<?php

namespace App\Domain\Billing\Services;

use App\Exceptions\Billing\StorageLimitReachedException;
use App\Models\Organization;

class StorageLimitService
{
    public function ensureCanUpload(
        Organization $organization,
        int $bytes,
    ): void {

        if (
            ! $organization
                ->canUpload($bytes)
        ) {
            throw new StorageLimitReachedException();
        }
    }

    public function increaseUsage(
        Organization $organization,
        int $bytes,
    ): void {

        $organization->increment(
            'storage_used_bytes',
            $bytes
        );
    }

    public function decreaseUsage(
        Organization $organization,
        int $bytes,
    ): void {

        $organization->decrement(
            'storage_used_bytes',
            $bytes
        );
    }
}
