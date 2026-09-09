<?php

namespace App\Exceptions;

use RuntimeException;

class LockedResourceException extends RuntimeException
{
    public function __construct(
        string $message = 'This resource is locked because your current plan limit has been reached.',
    ) {
        parent::__construct($message);
    }
}
