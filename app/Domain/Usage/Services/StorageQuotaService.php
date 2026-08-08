<?php

namespace App\Domain\Usage\Services;

use App\Exceptions\StorageQuotaExceededException;
use App\Models\Organization;

class StorageQuotaService
{
    /**
     * Ensure the organization has enough storage
     * available for the incoming file.
     */
    public function ensureCanStore(
        Organization $organization,
        int $bytes,
    ): void {
        if ($bytes < 0) {
            throw new \InvalidArgumentException(
                'File size cannot be negative.'
            );
        }

        $limit = $organization
            ->currentPlan()
            ?->max_storage_mb;

            $limit = $limit ? $limit * 1024 * 1024 : null;

        $currentUsage = $organization
            ->usage()
            ->firstOrCreate()
            ->storage_used_bytes;

        if (!$organization->canUpload($bytes)) {
            throw new StorageQuotaExceededException(
                currentUsage: $currentUsage,
                requestedBytes: $bytes,
                storageLimit: $limit,
            );
        }
    }
}
