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


    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function project(): HasMany
    {
        return $this->hasMany(Project::class);
    }

   
}
