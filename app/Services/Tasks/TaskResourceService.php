<?php

namespace App\Services\Tasks;

use App\Actions\Tasks\GetTaskResourcesAction;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskResourceService
{
    /*
|--------------------------------------------------------------------------
| Future
|--------------------------------------------------------------------------
|
| TODO:
| Filter out resources that are not visible to the
| current authenticated user.
|
| TODO:
| Support filtering by:
| - MIME type
| - Upload date
| - Uploader
|
| TODO:
| Full-text search for large projects.
|
*/

    /**
     * Resources available to attach to a task.
     */
    public function availableResources(
        Task $task,
    ): Collection {

        return $this

            ->availableQuery($task)

            ->latest()

            ->get();
    }
    /** Resources available for project */

    public function projectResources(
        Project $project,
    ): Collection {
        return $project
            ->fileAttachments()
            ->latest()
            ->get();
    }

    /**
     * Resources already attached.
     */
    public function attachedResources(
        Task $task
    ): Collection {
        return app(GetTaskResourcesAction::class)

            ->handle(
                $task
            );
    }

    /**
     * Search project resources.
     */
    public function search(
        Task $task,
        ?string $search = null,
    ): Collection {
        $query = $this->availableQuery($task);

        if ($search) {
            $query->whereHas(
                'storedFile',
                function ($query) use ($search) {

                    $query->where(
                        'original_filename',
                        'like',
                        "%{$search}%"
                    );
                }
            );;
        }

        return $query

            ->latest()

            ->get();
    }

    protected function availableQuery(
        Task $task,
    ) {
        return $task

            ->project

            ->fileAttachments()

            ->whereDoesntHave(

                'taskReferences',

                fn($query) => $query->where(
                    'task_id',
                    $task->id,
                ),
            );
    }
}
