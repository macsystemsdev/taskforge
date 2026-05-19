<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;

class OrganizationService
{
    public function create(array $data, User $owner): Organization
    {
        $organization = Organization::create([
        'owner_id' => $owner->id,
        'name' => $data['name'],
        'slug' => Str::slug($data['name']),
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ]);

    $organization->members()->attach($owner->id, [
        'role' => 'owner',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    app(WorkspaceService::class)->createDefaultWorkspace(
        organization: $organization
    );

    return $organization;
    }
}