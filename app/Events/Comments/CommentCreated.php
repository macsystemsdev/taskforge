<?php

namespace App\Events\Comments;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Comment $comment,
        public Project $project,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel(
                "presence-project.{$this->project->id}"
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.created';
    }

    public function broadcastWith(): array
    {
        return [
            'comment' => [
                'id' => $this->comment->id,
                'user_id' => $this->comment->user_id,
            ],
        ];
    }
}