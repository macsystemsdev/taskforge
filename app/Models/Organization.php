<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Table('organizations')]
#[Fillable(['name', 'slug', 'subscription_plan', 'subscription_status', 'owner_id'])]
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // morph activity logs
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
