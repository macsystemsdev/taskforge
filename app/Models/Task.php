<?php

namespace App\Models;

use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Notification;

#[Table('tasks')]
#[Fillable(['project_id', 'slug', 'assignee_id', 'creator_id', 'title', 'description', 'status', 'due_date', 'completed_at'])]
class Task extends Model
{
    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
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

    public function canBeAssignedTo(
        User $user
    ): bool {
        return $this->project
            ->team
            ->members()
            ->where(
                'users.id',
                $user->id
            )
            ->exists();
    }

    public function isOverdue(): bool
    {
        return $this->status !== TaskStatus::DONE
            && $this->status !== TaskStatus::CANCELLED
            && $this->due_date !== null
            && now()->greaterThan($this->due_date);
    }

    public function isBlocked(): bool
    {
        return $this->status->isBlocked();
    }

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function isCompleted(): bool
    {
        return $this->status->isDone();
    }

    public function isDueSoon(
        int $days = 3
    ): bool {

        return $this->status !== TaskStatus::DONE
            && $this->status !== TaskStatus::CANCELLED
            && $this->due_date !== null
            && now()->diffInDays(
                $this->due_date,
                false
            ) <= $days
            && now()->lessThanOrEqualTo(
                $this->due_date
            );
    }

    public function scopeOpen($query)
    {
        return $query->whereIn(
            'status',
            [
                TaskStatus::TODO,
                TaskStatus::IN_PROGRESS,
                TaskStatus::BLOCKED,
            ]
        );
    }

    public function scopeBlocked($query)
    {
        return $query->where(
            'status',
            TaskStatus::BLOCKED,
        );
    }
    public function scopeCompleted(
        $query
    ) {
        return $query->where(
            'status',
            TaskStatus::DONE
        );
    }

    public function scopeInProgress(
        $query
    ) {
        return $query->where(
            'status',
            TaskStatus::IN_PROGRESS
        );
    }

    public function scopeCancelled(
        $query
    ) {
        return $query->where(
            'status',
            TaskStatus::CANCELLED
        );
    }

    public function scopeOverdue(
        $query
    ) {
        return $query
            ->open()
            ->whereNotNull('due_date')
            ->where(
                'due_date',
                '<',
                now()
            );
    }

    public function scopeDueSoon(
        $query,
        int $days = 3
    ) {
        return $query
            ->open()
            ->whereNotNull('due_date')
            ->whereBetween(
                'due_date',
                [
                    now(),
                    now()->addDays($days),
                ]
            );
    }

    public function leadershipRecipients()
    {
        return $this
            ->project
            ->workspace
            ->organization
            ->administratorUsers();
    }

    public function leadershipRecipientsExcept(
        User $user
    ) {
        return $this
            ->leadershipRecipients()
            ->reject(
                fn($member) =>
                $member->id === $user->id
            );
    }

    public function notifyLeadership(
        object $notification,
        ?User $except = null
    ): void {

        $recipients =
            $except
            ? $this
            ->leadershipRecipientsExcept(
                $except
            )
            : $this
            ->leadershipRecipients();

        Notification::send(
            $recipients,
            $notification
        );
    }

    public function notifyAssignee(
        object $notification
    ): void {

        $this->assignee?->notify(
            $notification
        );
    }
}
