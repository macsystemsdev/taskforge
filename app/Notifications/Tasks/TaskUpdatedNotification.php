<?php

namespace App\Notifications\Tasks;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function backoff(): array
    {
        return [60, 300, 600];
    }
    /**
     * Create a new notification instance.
     */
    public function __construct(public Task $task)
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

            'title' => __('Task updated'),

            'task_id' => $this->task->id,

            'task_title' => $this->task->title,

            'message' => __(
                ':task was updated.',
                [
                    'task' => $this->task->title,
                ]
            ),

            'icon' => 'clipboard-document-list',

            'url' => route(
                'tasks.show',
                [
                    'workspace' =>
                    $this->task
                        ->project
                        ->workspace,

                    'project' =>
                    $this->task
                        ->project,

                    'task' =>
                    $this->task,
                ]
            ),
        ];
    }
}
