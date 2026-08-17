<?php

namespace App\Notifications\Invitations;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationRejectedNotification extends Notification implements ShouldQueue
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
    public function __construct(public User $user, public Organization $organization)
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
        return ['mail', 'database', 'broadcast'];
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
        $data = $this->notificationData();

        return (new MailMessage)
            ->subject($data['title'])
            ->line($data['message'])
            ->action('View Notification', $data['url']);
    }

    protected function notificationData(): array
    {
        return [
            'title' =>
            'Invitation Rejected',

            'message' =>
            "{$this->user->name} rejected your invitation to {$this->organization->name}.",

            'icon' =>
            'user-x',

            'url' =>
            route(
                'organizations.show',
                $this->organization
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
