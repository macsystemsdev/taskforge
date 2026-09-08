<?php

namespace App\Actions\Tasks;

use App\Domain\Task\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class BlockTaskAction
{
    public function handle(Task $task, string $reason): void
    {
        DB::transaction(function () use ($task, $reason) {
            $task->update([
                'status' => TaskStatus::BLOCKED,
                'blocked_reason' => $reason,
                'blocked_at' => now(),
            ]);

            $task->activityLogs()->create([
                'user_id' => auth()->id(),
                'event' => 'blocked',
                'description' => $reason,
            ]);
        });
    }
}