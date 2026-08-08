<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('organization_usages')]
#[Fillable(['organization_id', 'members_count', 'projects_count','workspaces_count','teams_count', 'tasks_count', 'storage_used_bytes', 'stored_files_count', 'voice_notes_count'])]
class OrganizationUsage extends Model
{
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }


}
