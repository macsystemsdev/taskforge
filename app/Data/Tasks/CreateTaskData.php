<?php

namespace App\Data\Tasks;

use Spatie\LaravelData\Data;

class CreateTaskData extends Data
{
    public function __construct(
        public string $title,

        public ?string $description,

        public ?int $assigneeId,

        public ?string $dueDate,
    ) {}
}
