<?php

namespace App\Notifications\Teams;

use App\Domain\Teams\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
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
        return ['database', 'broadcast'];
    }

    public function viaQueues(): array
    {
        return [
            'database' => 'notifications',
            'broadcast' => 'notifications',
            'mail' => 'emails',
        ];
    }

    protected function notificationData(): array
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->notificationData();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(
            $this->notificationData()
        );
    }
}
