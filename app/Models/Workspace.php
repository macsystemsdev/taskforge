<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('workspaces')]
#[Fillable(['name', 'description', 'organization_id'])]
class Workspace extends Model
{
    

public function organization_workspace(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
