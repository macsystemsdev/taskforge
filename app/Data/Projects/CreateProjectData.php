<?php

namespace App\Data\Projects;

class CreateProjectData
{
    public function __construct(
        public int $owner_id,
        public string $name,
        public ?string $description,
        public ?string $due_date,
    ) {}
}
