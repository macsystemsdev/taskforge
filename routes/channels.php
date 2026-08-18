<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('presence-project.{projectId}', function (User $user, $projectId) {
    $project = Project::findOrFail($projectId);
    
    if (! $user->can('view', $project)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});