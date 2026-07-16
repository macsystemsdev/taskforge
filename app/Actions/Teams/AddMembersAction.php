<?php

namespace App\Actions\Teams;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Data\Teams\AddMembersData;
use App\Domain\Teams\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use App\Notifications\Teams\TeamMemberAddedNotification;

class AddMembersAction
{

    public function __construct(
        protected CreateActivityLogAction $logAction,
    ) {}

    public function handle(
        Team $team,
        AddMembersData $data,
    ): void {

        $attachData = collect($data->memberIds)
            ->mapWithKeys(fn($memberId) => [
                $memberId => [
                    'role' => TeamRole::MEMBER,
                ],
            ])
            ->toArray();

        $team->memberships()
            ->syncWithoutDetaching($attachData);

        $members = User::whereIn(
            'id',
            $data->memberIds
        )->get();

        foreach ($members as $member) {

            $member->notify(

                new TeamMemberAddedNotification(
                    team: $team,
                    role: TeamRole::MEMBER,
                    addedBy: auth()->user()
                )
            );
        }

        $this->logAction->handle(
            subject: $team,
            event: 'members_added_to_team',
            properties: [
                'member_ids' => $data->memberIds,
            ],
        );
    }
}
