<?php

namespace App\Livewire\Comments;

use App\Actions\Comments\CreateCommentAction;
use App\Domain\Storage\Actions\DeleteFileAttachmentAction;
use App\Domain\Storage\Actions\UploadProjectAttachmentAction;
use App\Domain\Storage\Services\FileStorageService;
use App\Domain\Storage\Services\GetProjectAttachmentsService;
use App\Models\FileAttachment;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class CommentSection extends Component
{
    use WithFileUploads;

    public Project $commentable;
    public string $content = '';
    public array $uploads = [];
    public bool $uploadSuccess = false;
    public bool $ready = false;
    public int $attachmentsLimit = 5;
    public int $commentsLimit = 10;
    public ?int $index = null;

    protected array $rules = [
        'content' => ['required', 'string'],
        'uploads.*' => ['file', 'max:10240'],
    ];

    public function mount(Project $commentable): void
    {
        $this->commentable = $commentable;
    }

    public function render(GetProjectAttachmentsService $attachmentsService)
    {
        $commentsQuery = $this->commentable
            ->comments()
            ->with('user')
            ->latest();

        $commentsTotal = $commentsQuery->count();
        $comments = $commentsQuery->limit($this->commentsLimit)->get()->reverse()->values();

        $attachments = null;

        if ($this->ready) {
            $result = $attachmentsService->handle(
                $this->commentable,
                null,
                'newest',
                'all',
                $this->attachmentsLimit,
            );

            // Normalize to a collection for the blade; when paginated,
            // retrieve the collection items so we only load the first N.
            if ($result instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $attachments = collect($result->items());
                $attachmentsTotal = $result->total();
            } else {
                $attachments = $result;
                $attachmentsTotal = $attachments->count();
            }

            // Add preview URLs where applicable
            $storage = app(FileStorageService::class);

            $attachments = $attachments->map(function ($attachment) use ($storage) {
                $file = $attachment->storedFile;

                if ($file && (str_starts_with($file->mime_type, 'image/') || $file->mime_type === 'application/pdf')) {
                    $attachment->preview_url = $storage->url($file->path);
                } else {
                    $attachment->preview_url = null;
                }

                return $attachment;
            });

            // Notify the browser that attachments were loaded (for JS listeners)
            $this->dispatch('attachmentsLoaded', [
                'total' => $attachmentsTotal ?? $attachments->count(),
            ]);
        }

        return view('livewire.comments.comment-section', [
            'comments' => $comments,
            'commentsTotal' => $commentsTotal,
            'attachments' => $attachments,
            'attachmentsTotal' => $attachmentsTotal ?? null,
        ]);
    }

    public function loadAttachments(): void
    {
        $this->ready = true;
    }

    public function loadMoreAttachments(): void
    {
        $this->attachmentsLimit = $this->attachmentsLimit + 10;
    }

    public function loadMoreComments(): void
    {
        $this->commentsLimit = $this->commentsLimit + 10;
    }

    public function createComment(CreateCommentAction $action): void
    {
        $validated = $this->validate([
            'content' => ['required', 'string'],
        ]);

        $comment = $action->handle(
            commentable: $this->commentable,
            content: $validated['content'],
        );

        $this->reset('content');

        $this->dispatch('commentCreated', [
            'id' => $comment->id,
        ]);
    }

    public function uploadAttachments(UploadProjectAttachmentAction $action): void
    {
        if (empty($this->uploads)) {
            $this->addError('uploads', __('Please choose at least one file.'));

            return;
        }

        try {
            $uploaded = count($this->uploads);

            foreach ($this->uploads as $upload) {
                $action->handle(
                    project: $this->commentable,
                    file: $upload,
                    user: Auth::user(),
                );
            }

            $this->reset(['uploads']);
            $this->uploadSuccess = true;

            // Notify the browser/UI immediately that uploads completed
            $this->dispatch('attachmentsUploaded', [
                'count' => $uploaded,
            ]);
        } catch (ValidationException $exception) {
            // Dispatch a browser event so the UI can show an error toast
            $message = null;

            try {
                $errors = $exception->errors();
                $message = is_array($errors) ? array_values(array_map(function ($v) {
                    return is_array($v) ? implode(' ', $v) : $v;
                }, $errors))[0] ?? $exception->getMessage() : $exception->getMessage();
            } catch (\Throwable $e) {
                $message = $exception->getMessage();
            }

            $this->dispatch('attachmentsUploadFailed', [
                'message' => $message,
                'errors' => $exception->errors(),
            ]);

            throw $exception;
        }
    }

    public function deleteAttachment(int $attachmentId, DeleteFileAttachmentAction $action): void
    {
        $attachment = $this->commentable
            ->fileAttachments()
            ->where('id', $attachmentId)
            ->firstOrFail();

        Gate::authorize('update', $this->commentable);

        $action->handle(
            attachment: $attachment,
            user: Auth::user(),
        );
    }

    public function updatedUploads(): void
    {
        $this->uploadSuccess = false;
    }

    public function previewUrl(FileAttachment $attachment): string
    {
        return app(FileStorageService::class)->url(
            $attachment->storedFile->path,
        );
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function attachmentIcon(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return '🖼️';
        }

        if ($mimeType === 'application/pdf') {
            return '📄';
        }

        if (str_contains($mimeType, 'spreadsheet') || str_contains($mimeType, 'excel') || str_contains($mimeType, 'csv')) {
            return '📊';
        }

        if (str_contains($mimeType, 'word') || str_contains($mimeType, 'document')) {
            return '📝';
        }

        return '📎';
    }

    public function removeUpload($index)
    {
        $this->uploads = array_filter($this->uploads, function ($key) use ($index) {
            return $key != $index;
        }, ARRAY_FILTER_USE_KEY);

        $this->uploads = array_values($this->uploads);
    }
}
