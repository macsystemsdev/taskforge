<?php

namespace App\Domain\Storage\Actions;

use App\Actions\ActivityLogs\CreateActivityLogAction;
use App\Domain\Storage\Rules\FileUploadRules;
use App\Domain\Storage\Support\StoragePath;
use App\Models\FileAttachment;
use App\Models\Project;
use App\Models\User;
use App\Services\Storage\ValidateIncomingFileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UploadProjectAttachmentAction
{
    public function __construct(
        protected ValidateIncomingFileService $validator,
        protected UploadStoredFileAction $upload,
        protected CreateActivityLogAction $activity,

    ) {}

    public function handle(
        Project $project,
        UploadedFile $file,
        User $user
    ) {
        return DB::transaction(function () use ($project, $file, $user) {

            $this->validator->handle(
                $file,
                FileUploadRules::standard(),
            );

            $path = StoragePath::projectAttachments($project);

            $stored = $this->upload->handle(
                $file,
                $path,
                $project->workspace->organization,
                $user->id,
            );

            $attachment = $project->fileAttachments()->create([
                'stored_file_id' => $stored->id,
                'comment_id' => null,
                'created_by' => $user->id,
            ]);

            $this->activity->handle(

                event: 'project_file_uploaded',

                subject: $project,

                properties: [

                    'stored_file_id' => $stored->id,

                    'filename' => $stored->original_filename,

                    'uploaded_by' => $user->id,

                ],

            );

             return $attachment;
        });
       
    }
}
