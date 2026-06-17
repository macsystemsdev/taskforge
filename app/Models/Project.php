<?php

namespace App\Models;

use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Task\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Table('projects')]

#[Fillable([
    'workspace_id',
    'team_id',
    'created_by',
    'name',
    'slug',
    'description',
    'status',
    'due_date',
])]

class Project extends Model
{
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }


    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    // Use slug for route model binding
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(
            Comment::class,
            'commentable'
        );
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(
            ActivityLog::class,
            'subject'
        );
    }

    public function hasIncompleteTasks(): bool
    {
        return $this->tasks()
            ->whereNot('status', TaskStatus::DONE)
            ->exists();
    }

    protected $casts = [
        'status' => ProjectStatus::class,
        'due_date' => 'date',
    ];

    public function activeTaskCount(): int
    {
        return $this->tasks()
            ->open()
            ->count();
    }


    public function cancelledTaskCount(): int
    {
        return $this->tasks()
            ->cancelled()
            ->count();
    }

    public function overdueTaskCount(): int
    {
        return $this->tasks()
            ->whereNotIn('status', [
                TaskStatus::DONE,
                TaskStatus::CANCELLED,
            ])
            ->where('due_date', '<', now())
            ->count();
    }

    public function completedTaskCount(): int
    {
        return $this->tasks()
            ->where('status', TaskStatus::DONE)
            ->count();
    }

    public function dueSoonTaskCount(): int
    {
        return $this->tasks()
            ->whereNotIn('status', [
                TaskStatus::DONE,
                TaskStatus::CANCELLED,
            ])
            ->whereBetween('due_date', [
                now(),
                now()->addDays(3),
            ])
            ->count();
    }

    public function hasOverdueTasks(): bool
    {
        return $this->tasks()
            ->whereNotIn('status', [
                TaskStatus::DONE,
                TaskStatus::CANCELLED,
            ])
            ->where('due_date', '<', now())
            ->exists();
    }

    public function hasUpcomingDeadlines(): bool
    {
        return $this->tasks()
            ->whereNotIn('status', [
                TaskStatus::DONE,
                TaskStatus::CANCELLED,
            ])
            ->whereBetween('due_date', [
                now(),
                now()->addDays(3),
            ])
            ->exists();
    }

    public function isOverdue(): bool
    {
        if (! $this->due_date) {
            return false;
        }

        return $this->status->isActive()
            && $this->due_date->isPast();
    }
}
