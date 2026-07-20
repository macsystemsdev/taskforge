<?php

namespace App\Models;

use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Organizations\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Notification;
use Override;

#[Table('organizations')]
#[Fillable(['name', 'slug', 'subscription_plan', 'subscription_status', 'owner_id', 'stripe_customer_id', 'stripe_payment_method_id'])]
class Organization extends Model
{


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(OrganizationUser::class)
            ->withPivot([
                'role',
                'status',
                'joined_at',
                'invited_by',
            ])
            ->withTimestamps();
    }

    public function administrators()
    {
        return $this->members()
            ->wherePivotIn(
                'role',
                ['owner', 'admin']
            );
    }

    public function administratorUsers()
    {
        return $this->administrators()->get();
    }

    public function administratorUsersExcept(
        User $user
    ) {
        return $this
            ->administratorUsers()
            ->reject(
                fn($member) =>
                $member->id === $user->id
            );
    }

    public function notifyAdministrators(
        object $notification,
        ?User $except = null
    ): void {

        $recipients =
            $except
            ? $this->administratorUsersExcept(
                $except
            )
            : $this->administratorUsers();

        Notification::send(
            $recipients,
            $notification
        );
    }

    public function owner()
    {
        return $this->members()
            ->wherePivot(
                'role',
                'owner'
            )
            ->first();
    }

    public function notifyOwner(
        object $notification
    ): void {

        $this->owner()?->notify(
            $notification
        );
    }

    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    // Define a hasManyThrough relationship to get all projects associated with the organization through workspaces

    public function projects(): HasManyThrough
    {
        return $this->hasManyThrough(
            Project::class,
            Workspace::class
        );
    }

    // Define a hasManyThrough relationship to get all teams associated with the organization through workspaces
    public function teams(): HasManyThrough
    {
        return $this->hasManyThrough(
            Team::class,
            Workspace::class,
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // morph activity logs
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    // Organization  subscription  relationship

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function subscriptionPlan()
    {
        return $this->hasOneThrough(
            SubscriptionPlan::class,
            Subscription::class,
            'organization_id',
            'id',
            'id',
            'subscription_plan_id'
        );
    }


    public function hasStripeCustomer(): bool
    {
        return filled($this->stripe_customer_id);
    }

    public function tasks()
    {
        return Task::query()
            ->whereHas(
                'project.workspace',
                fn($query) =>
                $query->where(
                    'organization_id',
                    $this->id
                )
            );
    }

    // Role-based access control for organization members

    public function roleFor(User $user): ?OrganizationRole
    {
        $membership = $this->members()
            ->where('users.id', $user->id)
            ->first();

        return $membership?->pivot->role;
    }

    // Check if the user has a specific role in the organization
    public function hasRole(
        User $user,
        OrganizationRole $role
    ): bool {
        return $this->roleFor($user) === $role;
    }


    // check if user is owner of organization
    public function isOwner(
        User $user
    ): bool {
        return $this->roleFor($user)
            === OrganizationRole::OWNER;
    }

    // check if user is admin of organization
    public function isAdmin(
        User $user
    ): bool {
        return $this->roleFor($user)
            === OrganizationRole::ADMIN;
    }

    // Check if the organization is within the limit of a specific resource based on the subscription plan
    private function withinLimit(
        int $currentCount,
        ?int $limit,
    ): bool {
        return $limit === null || $currentCount < $limit;
    }


    // Check if the organization can create a new workspace, project, or add a new member based on the subscription plan limits

    public function currentPlan(): ?SubscriptionPlan
    {
        return $this->subscription
            ?->accessPlan();
    }

    public function canCreateWorkspace(): bool
    {
        return $this->withinLimit(
            $this->workspaces()->count(),
            $this->currentPlan()?->max_workspaces,
        );
    }

    public function canCreateProject(): bool
    {
        return $this->withinLimit(
            $this->projects()->count(),
            $this->currentPlan()?->max_projects,
        );
    }

    public function canCreateTask(): bool
    {
        return $this->withinLimit(
            $this->tasks()->count(),
            $this->currentPlan()?->max_tasks,
        );
    }

    public function canCreateTeam(): bool
    {
        return $this->withinLimit(
            $this->teams()->count(),
            $this->currentPlan()?->max_teams,
        );
    }


    public function canAddMember(): bool
    {
        return $this->withinLimit(
            $this->members()->count(),
            $this->currentPlan()?->max_members,
        );
    }

    public function canUpload(
        int $bytes
    ): bool {

        $limit =
            $this->currentPlan()
            ?->max_storage_mb;

        if (is_null($limit)) {
            return true;
        }

        return ($this->storage_used_bytes + $bytes)

            <=

            ($limit * 1024 * 1024);
    }
    // Check if the organization feature usage based on subscription plan
    public function workspaceUsage(): int
    {
        return $this->workspaces()->count();
    }

    public function projectUsage(): int
    {
        return $this->projects()->count();
    }

    public function teamUsage(): int
    {
        return $this->teams()->count();
    }

    public function memberUsage(): int
    {
        return $this->members()->count();
    }

    public function storageUsageMb(): float
    {
        return round(
            ($this->storage_used_bytes ?? 0)
                / 1024
                / 1024,
            2
        );
    }

    public function taskUsage(): int
    {
        return $this->tasks()->count();
    }
    // check locked workspaces based on subscription plan limit
    public function lockedWorkspaces()
    {
        $limit = $this->currentPlan()?->max_workspaces;

        if ($limit === null) {
            return collect();
        }

        return $this->workspaces
            ->sortBy('created_at')
            ->slice($limit);
    }

    public function lockedProjects()
    {
        $limit = $this->currentPlan()?->max_projects;

        if ($limit === null) {
            return collect();
        }

        return $this->projects
            ->sortBy('created_at')
            ->slice($limit);
    }

    public function lockedTasks()
    {
        $limit = $this->currentPlan()?->max_tasks;

        if ($limit === null) {
            return collect();
        }

        return $this->projects()
            ->with('tasks')
            ->get()
            ->pluck('tasks')
            ->flatten()
            ->sortBy('created_at')
            ->slice($limit);
    }

    // check locked features based on subscription plan limit

    public function workspaceLocked(
        Workspace $workspace
    ): bool {
        return $this
            ->lockedWorkspaces()
            ->contains(
                fn($lockedWorkspace) =>
                $lockedWorkspace->id === $workspace->id
            );
    }

    public function teamLocked(
        Team $team
    ): bool {
        return
            $this->workspaceLocked(
                $team->workspace
            )
            ||
            $this->lockedTeams()
            ->contains(
                fn($locked) =>
                $locked->id === $team->id
            );
    }

    public function projectLocked(
        Project $project
    ): bool {
        return
            $this->workspaceLocked(
                $project->workspace
            )
            ||
            $this->teamLocked(
                $project->team
            )
            ||
            $this->lockedProjects()
            ->contains(
                fn($locked) =>
                $locked->id === $project->id
            );
    }



    public function taskLocked(
        Task $task
    ): bool {
        return
            $this->projectLocked(
                $task->project
            );
    }


    public function lockedTeams()
    {
        $limit = $this->currentPlan()?->max_teams;

        if ($limit === null) {
            return collect();
        }

        return $this->teams
            ->sortBy('created_at')
            ->slice($limit);
    }


    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function isSubscribedTo(
        SubscriptionPlan $plan,
    ): bool {
        return $this->subscription->plan->is($plan);
    }

    public function latestSuccessfulTransaction(): ?PaymentTransaction
    {
        return $this->paymentTransactions()
            ->where('status', PaymentStatus::SUCCESSFUL)
            ->latest('paid_at')
            ->first();
    }

    // Orgnaization Metrics

    
}
