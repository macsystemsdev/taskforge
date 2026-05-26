<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('workspaces')]
#[Fillable(['name', 'description', 'organization_id', 'slug'])]
class Workspace extends Model
{


    public function organization_workspace(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
