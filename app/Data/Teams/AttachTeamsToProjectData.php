<?php

namespace App\Data\Teams;

use Spatie\LaravelData\Data;

class AttachTeamsToProjectData extends Data
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public array $team_ids,
    ) {}
}
