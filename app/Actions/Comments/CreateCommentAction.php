<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class CreateCommentAction
{
    public function handle(
        Model $commentable,
        string $content,
    ): Comment {

        return $commentable->comments()->create([
            'user_id' => Auth::id(),

            'content' => $content,
        ]);
    }
}
