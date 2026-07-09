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

        public ?string $reference = null,

        public ?string $message = null,

        public array $metadata = [],
    ) {}

    public static function successful(
        string $reference,
        array $metadata = [],
    ): self {
        return new self(
            successful: true,
            reference: $reference,
            metadata: $metadata,
        );
    }

    public static function failed(
        string $message,
    ): self {
        return new self(
            successful: false,
            message: $message,
        );
    }
}
