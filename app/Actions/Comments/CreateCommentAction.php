<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\FileAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class CreateCommentAction
{
    public function handle(
        Model $commentable,
        string $content,
    ): Comment {

        $comment = $commentable->comments()->create([
            'user_id' => Auth::id(),

            'content' => $content,
        ]);

        // Attach any recent orphaned file uploads the same user made to this
        // comment. We only consider files created in the last hour to avoid
        // accidentally linking much older uploads.
        $oneHourAgo = now()->subHour();

        FileAttachment::where('attachable_type', $commentable->getMorphClass())
            ->where('attachable_id', $commentable->getKey())
            ->where('created_by', Auth::id())
            ->whereNull('comment_id')
            ->where('created_at', '>=', $oneHourAgo)
            ->update([
                'comment_id' => $comment->id,
            ]);

        return $comment;
    }
}
