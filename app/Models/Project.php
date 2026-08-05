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
use Illuminate\Support\Facades\Notification;

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

    public function attachments(): MorphMany
    {
        return $this->morphMany(
            FileAttachment::class,
            'attachable',
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

    public function inProgressTaskCount(): int
    {
        if (isset($this->in_progress_tasks_count)) {
            return $this->in_progress_tasks_count;
        }
        return $this->tasks()
            ->where(
                'status',
                TaskStatus::IN_PROGRESS,
            )
            ->count();
    }


    public function cancelledTaskCount(): int
    {
        if (isset($this->cancelled_tasks_count)) {
            return $this->cancelled_tasks_count;
        }
        return $this->tasks()
            ->cancelled()
            ->count();
    }

    public function overdueTaskCount(): int
    {
        if (isset($this->overdue_tasks_count)) {
            return $this->overdue_tasks_count;
        }

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
        if (isset($this->completed_tasks_count)) {
            return $this->completed_tasks_count;
        }

        return $this->tasks()
            ->where('status', TaskStatus::DONE)
            ->count();
    }

    public function dueSoonTaskCount(): int
    {
        if (isset($this->due_soon_tasks_count)) {
            return $this->due_soon_tasks_count;
        }

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

    public function hasBlockedTasks(): bool
    {
        return $this->tasks()
            ->blocked()
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

    public function blockedTaskCount(): int
    {
        if (isset($this->blocked_tasks_count)) {
            return $this->blocked_tasks_count;
        }
        return $this->tasks()
            ->blocked()
            ->count();
    }

    public function openTaskCount(): int
    {
        if (isset($this->open_tasks_count)) {
            return $this->open_tasks_count;
        }

        return $this->tasks()
            ->open()
            ->count();
    }

    public function totalTaskCount(): int
    {
        if (isset($this->total_tasks_count)) {
            return $this->total_tasks_count;
        }

        return $this->tasks()->count();
    }

    public function completionPercentage(): int
    {
        $total = $this->totalTaskCount();

        if ($total === 0) {
            return 0;
        }

        return (int) round(
            ($this->completedTaskCount() / $total) * 100,
        );
    }

    public function notificationRecipients()
    {
        return $this
            ->workspace
            ->organization
            ->administratorUsers();
    }

    public function notifyLeadership(
        object $notification
    ): void {

        Notification::send(
            $this
                ->notificationRecipients(),

            $notification
        );
    }
}
