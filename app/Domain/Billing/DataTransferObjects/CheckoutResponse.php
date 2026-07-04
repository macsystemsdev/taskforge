<?php

namespace App\Domain\Billing\DataTransferObjects;

readonly class CheckoutResponse
{
    public function __construct(
        public string $url,
        public string $reference,
        public array $metadata = [],
    ) {}
}
