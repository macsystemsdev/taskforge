<?php

namespace App\Notifications\Teams;

use App\Domain\Teams\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TeamMemberAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }
    public function __construct(
        public Team $team,
        public  TeamRole $role,
        public ?User $addedBy,
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function viaQueues(): array
    {
        return [
            'database' => 'notifications',
            'mail' => 'emails',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Team membership added'),

            'team_id' => $this->team->id,
            'team_name' => $this->team->name,

            'role' => $this->role->value,
            'role_label' => $this->role->label(),

            'added_by_id' => $this->addedBy?->id,
            'added_by_name' => $this->addedBy?->name,

            'message' => __(
                'You were added to the :team team.',
                [
                    'team' => $this->team->name,
                ]
            ),

            'icon' => 'users',

            'url' => route(
                'teams.show',
                [
                    'workspace' => $this->team->workspace,
                    'team' => $this->team,
                ]
            ),
        ];
    }
}
