<?php

namespace App\Domain\Billing\DataTransferObjects;

use Spatie\LaravelData\Data;

class PaymentResponse extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public bool $successful,

        public ?string $reference,

        public array $metadata = [],
    ) {}
}
