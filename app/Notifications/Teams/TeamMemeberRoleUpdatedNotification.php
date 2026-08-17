<?php

namespace App\Notifications\Teams;

use App\Domain\Teams\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemeberRoleUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

public function backoff(): array
{
    return [10,30,60];
}
    /**
     * Create a new notification instance.
     */
    public function __construct(public Team $team, public TeamRole $role, public User $updatedBy)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    protected function notificationData(): array
    {
        return [

            'title' => __('Team role updated'),

            'team_id' => $this->team->id,

            'team_name' => $this->team->name,

            'message' => __(
                'Your role in :team was changed to :role.',
                [
                    'team' => $this->team->name,
                    'role' => $this->role->label(),
                ]
            ),

            'icon' => 'shield-check',

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
