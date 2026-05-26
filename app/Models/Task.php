<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('tasks')]
#[Fillable(['project_id', 'assigned_to', 'created_by', 'title', 'description', 'status', 'priority', 'due_date', 'completed_at'])]
class Task extends Model
{
    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Scopes for filtering tasks
    public function scopeTodo($query)
    {
        return $query->where(
            'status',
            TaskStatus::TODO
        );
    }

    public function scopeInProgress($query)
    {
        return $query->where(
            'status',
            TaskStatus::IN_PROGRESS
        );
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull(
            'completed_at'
        );
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn(
            'priority',
            [
                TaskPriority::HIGH,
                TaskPriority::URGENT,
            ]
        );
    }
}
