<?php

namespace App\Data\Teams;

class CreateTeamData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}
}
