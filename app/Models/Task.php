<?php

namespace App\Models;

use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Table('tasks')]
#[Fillable(['project_id', 'slug', 'assigned_to', 'created_by', 'title', 'description', 'status', 'priority', 'due_date', 'completed_at'])]
class Task extends Model
{
    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
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

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where(
            'assigned_to',
            $userId
        );
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(
            Comment::class,
            'commentable'
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(
            ActivityLog::class,
            'subject'
        );
    }
}
