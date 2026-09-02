<?php

namespace App\Livewire\Projects;

use App\Domain\Storage\Services\GetProjectAttachmentsService;
use App\Domain\Storage\Services\FileStorageService;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectFiles extends Component
{
    use WithPagination;

    public Project $project;
    public string $search = '';
    public string $sort = 'newest';
    public string $type = 'all';
    public bool $ready = true;
    public int $perPage = 10;

    protected $listeners = [
        'attachmentsUploaded' => 'loadAttachments',
        'commentCreated' => 'loadAttachments',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'newest'],
        'type' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    protected string $paginationTheme = 'tailwind';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function loadAttachments(): void
    {
        $this->ready = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render(GetProjectAttachmentsService $attachmentsService)
    {
        $attachments = $this->ready
            ? $attachmentsService->handle(
                $this->project,
                $this->search,
                $this->sort,
                $this->type,
                $this->perPage,
            )
            : null;

        // Add preview URLs for images and PDFs using the storage service.
        if ($attachments) {
            $storage = app(FileStorageService::class);

            $map = function ($attachment) use ($storage) {
                $file = $attachment->storedFile;

                // Set preview URL for safe inline types
                if ($file && (str_starts_with($file->mime_type, 'image/') || $file->mime_type === 'application/pdf')) {
                    $attachment->preview_url = $storage->url($file->path);
                } else {
                    $attachment->preview_url = null;
                }

                // Add ZIP preview data
                if ($file && str_contains($file->mime_type, 'zip')) {
                    $attachment->zip_preview = $storage->previewZip($file->path);
                } else {
                    $attachment->zip_preview = null;
                }

                return $attachment;
            };

            if ($attachments instanceof LengthAwarePaginator) {
                $attachments->getCollection()->transform($map);
            } else {
                $attachments->transform($map);
            }
        }

        return view('livewire.projects.project-files', [
            'attachments' => $attachments,
        ]);
    }
}
