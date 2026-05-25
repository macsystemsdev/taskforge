<?php

namespace App\Services;

use App\Data\Organizations\CreateOrganizationData;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;

class OrganizationService
{
    public function create(CreateOrganizationData $data): Organization
    {
        $organization = Organization::create([
        'owner_id' => $data->owner_id,
        'name' => $data->name,
        'slug' => Str::slug($data->name),
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ]);

    $organization->members()->attach($data->owner_id, [
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