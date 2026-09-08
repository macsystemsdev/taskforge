<?php

namespace App\Actions\Tasks;

use App\Domain\Task\TaskStatus;
use App\Models\Task;
use DomainException;
use Illuminate\Support\Facades\DB;

class UnblockTaskAction
{
    public function handle(Task $task): void
    {
        if (! $task->status->canTransitionTo(TaskStatus::IN_PROGRESS)) {
            throw new DomainException('Task cannot be unblocked.');
        }

        DB::transaction(function () use ($task) {
            $task->update([
                'status' => TaskStatus::IN_PROGRESS,
                'blocked_reason' => null,
                'blocked_at' => null,
            ]);

            $task->activityLogs()->create([
                'user_id' => auth()->id(),
                'event' => 'unblocked',
                'description' => 'Task unblocked and moved back to in progress',
            ]);
        });
    }
}
