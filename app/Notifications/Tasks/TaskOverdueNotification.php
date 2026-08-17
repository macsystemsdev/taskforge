<?php

namespace App\Notifications\Tasks;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification implements ShouldQueue
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
        return ['database', 'mail', 'broadcast'];
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
            'Task Overdue',

            'message' =>
            "'{$this->task->title}' is overdue.",

            'icon' =>
            'exclamation-circle',

              'url' => route(
                'tasks.show',
                [
                    'workspace' =>
                    $this->task
                        ->project->workspace,

                    'project' =>
                    $this->task
                        ->project,

                    'task' =>
                    $this->task,
                ]
            ),

            'entity_type' => 'task',

            'entity_id' =>
            $this->task->id,

            'organization_id' =>
            $this->task
                ->project
                ->workspace
                ->organization_id,
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
