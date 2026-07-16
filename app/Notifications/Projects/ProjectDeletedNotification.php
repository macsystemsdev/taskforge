<?php

namespace App\Notifications\Projects;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectDeletedNotification extends Notification implements ShouldQueue
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
    public function __construct(public Project $project)
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
        return ['database'];
    }

    public function viaQueues(): array
{
    return [
        'database' => 'notifications',
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [

            'title' => __('Project deleted'),

            'project_name' => $this->project->name,

            'message' => __(
                ':project was deleted.',
                [
                    'project' =>
                    $this->project->name,
                ]
            ),

            'icon' => 'trash',

            'url' => route(
                'projects.index'
            ),
        ];
    }
}
