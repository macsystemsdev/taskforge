<?php

namespace App\Actions\Organizations;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Models\Organization;
use DomainException;

class DeleteOrganizationAction
{
    public function __construct(
        protected CreateActivityLogAction $activity
    ) {}

    public function handle(Organization $organization): void
    {
        if ($organization->workspaces()->exists()) {
            throw new DomainException(
                'Delete all workspaces before deleting the organization.'
            );
        }
        $organization->delete();

        $this->activity->handle(
            event: "{$organization->name} has been deleted",
            subject: $organization->id,
            properties: [
                'organization_name' => $organization->name,
            ]
        );
    }
}
