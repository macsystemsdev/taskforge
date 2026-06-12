<?php

namespace App\Domain\Task;

enum TaskStatus: string
{
    case TODO = 'todo';

    case IN_PROGRESS = 'in_progress';

    case CANCELLED = 'cancelled';

    case DONE = 'done';

    //case Blocked = 'blocked';

    public function canTransitionTo(
        TaskStatus $status
    ): bool {
        return match ($this) {

            self::TODO => in_array(
                $status,
                [
                    self::IN_PROGRESS,
                    self::CANCELLED,
                ]
            ),

            self::IN_PROGRESS => in_array(
                $status,
                [
                    self::TODO,
                    self::DONE,
                    self::CANCELLED,
                ]
            ),

            self::DONE => false,

            self::CANCELLED => false,
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::DONE;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isInProgress(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    public function isTodo(): bool
    {
        return $this === self::TODO;
    }

    public function canDelete(): bool
    {
        return in_array(
            $this,
            [
                TaskStatus::TODO,
                TaskStatus::CANCELLED,
            ]
        );
    }
}
