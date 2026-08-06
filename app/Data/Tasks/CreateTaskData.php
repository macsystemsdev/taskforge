<?php

namespace App\Data\Tasks;

use App\Domain\Task\TaskPriority;
use Spatie\LaravelData\Data;

class CreateTaskData extends Data
{
    public function __construct(
        public string $title,

        public ?string $description,

        public ?int $assigneeId,

        public ?string $dueDate,

        public string $priority,

        public array $resourceIds = [],
    ) {}
}
