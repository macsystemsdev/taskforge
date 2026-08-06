<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('task_file_references')]
#[Fillable([
    'task_id',
    'file_attachment_id',
    'created_by',
])]
class TaskFileReference extends Model
{
   /*
|--------------------------------------------------------------------------
| Future
|--------------------------------------------------------------------------
|
| TODO:
| Add ordering support so referenced resources can be arranged
| in a preferred execution order.
|
| TODO:
| Support optional annotations describing why a resource
| was attached to the task.
|
| TODO:
| Record reference history for offline synchronization and
| conflict resolution.
|
*/

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function fileAttachment(): BelongsTo
    {
        return $this->belongsTo(
            FileAttachment::class
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }
}