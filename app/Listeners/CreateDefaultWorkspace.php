<?php

namespace App\Listeners;

use App\Events\OrganizationCreated;
use App\Services\WorkspaceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDefaultWorkspace
{

    public function __construct(
        protected WorkspaceService $workspaceService,
    ) {}

    public function handle(OrganizationCreated $event): void
    {
        $this->workspaceService->createDefaultWorkspace(
            organization: $event->organization,
            workspace_name: $event->organization->name . ' Workspace'
        );
    }
}
