<?php

namespace App\Domain\Storage\Actions;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Storage\Services\FileStorageService;
use App\Domain\Usage\Actions\DecreaseStorageUsageAction;
use App\Models\FileAttachment;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeleteFileAttachmentAction
{
    public function __construct(
        protected FileStorageService $storage,
        protected CreateActivityLogAction $activity,
        protected DecreaseStorageUsageAction $decreaseStorageUsage,
    ) {}

    /**
     * Delete an attachment and its underlying stored file.
     *
     * Workflow:
     * 1. Remove the physical file from storage.
     * 2. Delete the attachment record.
     * 3. Delete the stored file metadata.
     * 4. Record an activity log.
     *
     * Future:
     * Before deleting the StoredFile record, verify that no other
     * attachments reference it to support shared files.
     */
    public function handle(
        FileAttachment $attachment,
        User $user,
    ): void {

        DB::transaction(function () use (
            $attachment,
            $user,
        ) {

            $storedFile = $attachment->storedFile;

            if (
                $this->storage->exists(
                    $storedFile->path,
                )
            ) {

                if (! $this->storage->delete(
                    $storedFile->path,
                )) {

                    throw new RuntimeException(
                        'Unable to delete the physical file.',
                    );
                }
            }

            $subject = $attachment->attachable;

            $properties = [
                'attachment_id' => $attachment->id,
                'stored_file_id' => $storedFile->id,
                'deleted_by' => $user->id,
                'filename' => $storedFile->original_filename,
            ];

            /*
            |--------------------------------------------------------------------------
            | Delete attachment metadata
            |--------------------------------------------------------------------------
            |
            | Delete the attachment first.
            |
            | Future versions may allow multiple attachments to reference
            | the same StoredFile. This ordering makes that evolution
            | straightforward.
            |
            */

            $attachment->delete();

            /*
            |--------------------------------------------------------------------------
            | Delete stored file metadata
            |--------------------------------------------------------------------------
            */

            $storedFile->delete();

            $this->decreaseStorageUsage->handle(
                $subject->workspace->organization,
                $storedFile->size,
            );

            /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

            $this->activity->handle(
                event: 'attachment_deleted',
                subject: $subject,
                properties: $properties,
            );
        });
    }
}
