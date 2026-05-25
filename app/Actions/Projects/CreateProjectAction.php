<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Support\Str;
use App\Data\Projects\CreateProjectData;

class CreateProjectAction
{
    public function handle(
        Workspace $workspace,
        CreateProjectData $data,
    ): Project {
        return $workspace->projects()->create([
            'owner_id' => $data->owner_id,
            'name' => $data->name,
            'slug' => Str::slug($data->name),
            'description' => $data->description,
            'status' => 'active',
            'due_date' => $data->due_date,
        ]);
    }
}
