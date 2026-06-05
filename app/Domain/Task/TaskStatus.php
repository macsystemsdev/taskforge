<?php

namespace App\Domain\Task;

enum TaskStatus: string
{
    case TODO = 'todo';

    case IN_PROGRESS = 'in_progress';

    case REVIEW = 'review';

    case DONE = 'done';

    //case Blocked = 'blocked';
}
