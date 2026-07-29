<?php

namespace App\Domain\Task;

enum TaskStatus: string
{
    case TODO = 'todo';

    case IN_PROGRESS = 'in_progress';

    case BLOCKED = 'blocked';

    case DONE = 'done';

    case CANCELLED = 'cancelled';

    public function canTransitionTo(
        TaskStatus $status,
    ): bool {

        return match ($this) {

            self::TODO => in_array(
                $status,
                [
                    self::IN_PROGRESS,
                    self::CANCELLED,
                ],
                true,
            ),

            self::IN_PROGRESS => in_array(
                $status,
                [
                    self::BLOCKED,
                    self::DONE,
                    self::CANCELLED,
                ],
                true,
            ),

            self::BLOCKED => in_array(
                $status,
                [
                    self::IN_PROGRESS,
                    self::CANCELLED,
                ],
                true,
            ),

            self::DONE => false,

            self::CANCELLED => false,
        };
    }

    public function isTodo(): bool
    {
        return $this === self::TODO;
    }

    public function isInProgress(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    public function isBlocked(): bool
    {
        return $this === self::BLOCKED;
    }

    public function isDone(): bool
    {
        return $this === self::DONE;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isOpen(): bool
    {
        return in_array(
            $this,
            [
                self::TODO,
                self::IN_PROGRESS,
                self::BLOCKED,
            ],
            true,
        );
    }

    public function canDelete(): bool
    {
        return in_array(
            $this,
            [
                self::TODO,
                self::CANCELLED,
            ],
            true,
        );
    }
}
