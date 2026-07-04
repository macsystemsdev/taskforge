<?php

namespace App\Models;

use App\Domain\Organizations\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Table('organizations')]
#[Fillable(['name', 'slug', 'subscription_plan', 'subscription_status', 'owner_id', 'stripe_customer_id'])]
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


    // Check if the organization can create a new workspace, project, or add a new member based on the subscription plan limits

    public function canCreateWorkspace(): bool
    {

        $limit = $this->subscription?->plan?->max_workspaces;

        return is_null($limit)
            || $this->workspaces()->count() < $limit;
    }

    public function canCreateProject(): bool

    {
        $limit = $this->subscription?->plan?->max_projects;

        return is_null($limit)
            || $this->projects()->count() < $limit;
    }

    public function canAddMember(): bool
    {
        $limit = $this->subscription?->plan?->max_members;

        return is_null($limit)
            || $this->members()->count() < $limit;
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

    // organization plan checking methods
    
}
