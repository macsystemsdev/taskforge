<?php

namespace App\Console\Commands\Task;

use App\Models\Task;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:task-overdue')]
#[Description('Command description')]
class TaskOverdue extends Command
{
    public function __construct(
        protected Task $task
    ) {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->task->notifyAssignee(
            new TaskOverdueNotification(
                $this->task
            )
        );

        $this->task->notifyLeadership(
            new TaskOverdueNotification(
                $this->task
            )
        );
    }
}
