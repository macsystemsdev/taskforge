<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('organizations')]
#[Fillable(['name', 'slug', 'subscription_plan', 'subscription_status', 'owner_id'])]
class Organization extends Model
{
    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function ownedWorkspace(): HasMany
    {
        return $this->hasMany(Workspace::class, 'workspace_id', 'id');
    }
}
