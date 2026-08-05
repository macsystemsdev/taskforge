<?php

namespace App\Domain\Storage\Actions;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Storage\Services\FileStorageService;
use App\Models\FileAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadProjectAttachmentAction
{
    public function __construct(
        protected FileStorageService $storage,
        protected CreateActivityLogAction $activity,
    ) {}

    public function handle(
        FileAttachment $attachment,
        User $user,
        ?Model $subject = null,
    ): StreamedResponse {

        $subject = $subject ?? $attachment->attachable;

        if (! $subject) {
            throw new \RuntimeException('Cannot log download activity without a valid subject.');
        }

        $storedFile = $attachment->storedFile;

        $this->activity->handle(
            event: 'project_file_downloaded',
            subject: $subject,
            properties: [
                'file_attachment_id' => $attachment->id,
                'stored_file_id' => $storedFile->id,
                'downloaded_by' => $user->id,
            ],
        );

        return $this->storage->download($storedFile->path, $storedFile->original_filename);
    }
}
