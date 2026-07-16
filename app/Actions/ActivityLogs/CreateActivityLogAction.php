<?php

namespace App\Actions\ActivityLogs;

use App\Jobs\LogActivityJob;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class CreateActivityLogAction
{
    public function handle(
        string $event,
        array $properties = [],
        Model $subject,
    ): void {

        LogActivityJob::dispatch(
            event: $event,
            properties: $properties,
            subjectType: $subject::class,
            subjectId: $subject->id,
            userId: auth()->id(),
        )
        ->onQueue('activities')
        ->delay(now()->addSeconds(5));
    }
}
