<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogActivityJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function backoff(): array
    {
        return [10];
    }
    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $event,
        public array $properties,
        public string $subjectType,
        public int $subjectId,
        public ?int $userId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subject = $this->subjectType::find(
            $this->subjectId
        );

        if (! $subject) {
            return;
        }

        $subject->activityLogs()->create([
            'user_id' => $this->userId,
            'event' => $this->event,
            'properties' => $this->properties,
        ]);
    }
}
