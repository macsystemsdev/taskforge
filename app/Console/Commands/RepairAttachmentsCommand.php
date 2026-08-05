<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\FileAttachment;
use Illuminate\Console\Command;

class RepairAttachmentsCommand extends Command
{
    protected $signature = 'attachments:repair';

    protected $description = 'Repair attachments that were incorrectly attached to comments instead of their parent project. This resets attachable_type/attachable_id to the commentable (project) when appropriate.';

    public function handle(): int
    {
        $this->info('Scanning for file attachments attached to comments...');

        $moved = 0;

        FileAttachment::where('attachable_type', Comment::class)
            ->whereNotNull('comment_id')
            ->chunkById(200, function ($attachments) use (&$moved) {
                foreach ($attachments as $attachment) {
                    try {
                        $comment = Comment::find($attachment->comment_id);

                        if (! $comment) {
                            continue;
                        }

                        $commentable = $comment->commentable;

                        // Only repair when the commentable is a Project (we only want
                        // project-level attachments to be visible in the project files view).
                        if ($commentable && $commentable::class === $commentable->getMorphClass()) {
                            // Note: some installations use morph class names rather than FQCNs
                        }

                        // If the commentable exists and is a Project-like model, reset
                        // the attachable to point to the commentable (usually the project).
                        if ($commentable && method_exists($commentable, 'attachments')) {
                            $attachment->attachable_type = $commentable->getMorphClass();
                            $attachment->attachable_id = $commentable->getKey();
                            $attachment->save();

                            $moved++;
                        }
                    } catch (\Throwable $e) {
                        $this->error('Failed to repair attachment id=' . $attachment->id . ' - ' . $e->getMessage());
                    }
                }
            });

        $this->info("Repaired {$moved} attachments.");

        return self::SUCCESS;
    }
}
