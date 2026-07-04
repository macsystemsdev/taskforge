<?php

namespace App\Listeners;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Events\OrganizationCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogOrganizationCreated
{

    public function __construct(
        protected CreateActivityLogAction $activity,
    ) {}

    public function handle(OrganizationCreated $event): void
    {
        $organization = $event->organization;

        $this->activity->handle(
            event: 'organization_created',
            subject: $organization,
            properties: [
                'organization_name' => $organization->name,
            ]
        );
    }
}
