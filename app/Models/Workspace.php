<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Override;

#[Table('workspaces')]
#[Fillable(['name', 'description', 'organization_id', 'slug'])]
class Workspace extends Model
{


    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    // activity log
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    // route by slug
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    
}
