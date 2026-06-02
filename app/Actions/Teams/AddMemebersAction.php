<?php

namespace App\Actions\Teams;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Teams\AddMemebersData;
use App\Models\Team;

class AddMemebersAction
{

    public function __construct(
        protected CreateActivityLogAction $logAction,
    ) {}
    public function handle(
    Team $team,
    AddMemebersData $data,
)
{
    $team->members()->syncWithoutDetaching(
        $data->member_ids
    );

    $this->logAction->handle(
        subject: $team,
        event: 'members_added_to_team',
        properties: [
            'member_ids' => $data->member_ids,
        ],
    );

    $this->logAction->handle(
        subject: $team,
        event: 'members_removed_from_team',
        properties: [
            'member_ids' => $data->member_ids,
        ],
    );
}
}
