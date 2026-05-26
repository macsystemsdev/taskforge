<?php

namespace App\Actions\Tasks;

use App\Data\Tasks\CreateTaskData;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class CreateTaskAction
{
    /**
     * Create a new class instance.
     */
    public function handle(
        CreateTaskData $data,
        Project $project,
    ) {
        return $project->tasks()->create([
            'created_by' => Auth::id(),

            'assigned_to' => $data->assigned_to,

            'title' => $data->title,

            'description' => $data->description,

            'priority' => $data->priority,

            'status' => 'todo',

            'due_date' => $data->due_date,
        ]);
    }
}
