<?php

namespace App\Services;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Organizations\CreateOrganizationData;
use App\Models\Organization;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationService
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function create(CreateOrganizationData $data): Organization
    {
        $slug = Str::slug($data->name);

        if (Organization::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => __('An organization with that name already exists.'),
            ]);
        }

        $organization = Organization::create([
            'owner_id' => $data->owner_id,
            'name' => $data->name,
            'slug' => $slug,
            'subscription_plan' => 'free',
            'subscription_status' => 'active',
        ]);

        $organization->members()->attach($data->owner_id, [
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // log created organization and owner
        $this->activity->handle(
            event: 'organization_created',
            subject: $organization,
            properties: [
                'organization_name' => $organization->name,
            ]
        );

        app(WorkspaceService::class)->createDefaultWorkspace(
            organization: $organization
        );

        return $organization;
    }
}
