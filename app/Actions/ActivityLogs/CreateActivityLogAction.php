<?php

namespace App\Actions\ActivityLogs;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class CreateActivityLogAction
{
    public function handle(
        string $event,
        array $properties = [],
        Model $subject,
    ): ActivityLog {
        return $subject->activityLogs()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'properties' => $properties,
        ]);
    }
}
