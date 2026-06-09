<?php

namespace App\Data\Teams;

use Spatie\LaravelData\Data;

class AddMembersData extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public int $leaderId,
        public array $memberIds = [],
    ) {}
}
