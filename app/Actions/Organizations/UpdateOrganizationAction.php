<?php

namespace App\Actions\Organizations;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Organizations\UpdateOrganizationData;
use App\Models\Organization;
use Illuminate\Support\Str;

class UpdateOrganizationAction
{
    public function __construct(
        protected CreateActivityLogAction $activity,
    ) {}

    public function handle(
        Organization $organization,
        UpdateOrganizationData $data
    ): Organization {

        $slug = Str::slug($data->name);

        $organization->update([
            'name' => $data->name,
            'slug' => $slug,
        ]);

        $this->activity->handle(
            event: 'organization_updated',
            properties: [
                'organization_name' => $organization->name,
            ],
            subject: $organization,
        );

        return $organization;
    }
}
