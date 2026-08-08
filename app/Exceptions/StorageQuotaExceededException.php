<?php

namespace App\Exceptions;

use RuntimeException;

class StorageQuotaExceededException extends RuntimeException
{
    public function __construct(
        public readonly int $currentUsage,
        public readonly int $requestedBytes,
        public readonly ?int $storageLimit,
    ) {
        parent::__construct(
            'The organization has exceeded its storage quota.'
        );
    }
}