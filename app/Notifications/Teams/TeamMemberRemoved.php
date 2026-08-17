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

class TeamMemberRemoved extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Team $team,
        public  TeamRole $role,
        public ?User $removedBy,
    ) {
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
            'title' => __('Removed from team'),

            'team_id' => $this->team->id,
            'team_name' => $this->team->name,

            'removed_by_id' => $this->removedBy?->id,
            'removed_by_name' => $this->removedBy?->name,

            'message' => __(
                'You were removed from the :team team.',
                [
                    'team' => $this->team->name,
                ]
            ),

            'icon' => 'users',

            'url' => route('notifications.index'),
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
