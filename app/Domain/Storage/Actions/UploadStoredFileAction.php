<?php

namespace App\Domain\Storage\Actions;

use App\Domain\Storage\Enums\FileCategory;
use App\Domain\Storage\Enums\FileVisibility;
use App\Domain\Storage\Services\FileStorageService;
use App\Models\Organization;
use App\Models\StoredFile;
use Illuminate\Http\UploadedFile;

class UploadStoredFileAction
{
    public function __construct(
        protected FileStorageService $storage,
    ) {}

    /**
     * Upload a file and persist its metadata.
     */
    public function handle(
        UploadedFile $file,
        string $directory,
        Organization $organization,
        int $uploadedBy,
    ): StoredFile {

        $path = $this->storage
            ->store(
                $file,
                $directory,
            );

        return StoredFile::create([
            'organization_id' => $organization->id,
            'uploaded_by' => $uploadedBy,
            'disk' => 'public',
            'path' => $path,
            'stored_name' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->extension(),
            'category' => $this->categoryFromMimeType($file->getMimeType()),
            'visibility' => FileVisibility::PROJECT,
            'size' => $file->getSize(),
            'checksum' => hash_file(
                'sha256',
                $file->getRealPath(),
            ),
        ]);
    }

    protected function categoryFromMimeType(string $mimeType): FileCategory
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => FileCategory::IMAGE,
            str_starts_with($mimeType, 'video/') => FileCategory::VIDEO,
            str_starts_with($mimeType, 'audio/') => FileCategory::AUDIO,
            str_contains($mimeType, 'spreadsheet') => FileCategory::SPREADSHEET,
            str_contains($mimeType, 'excel') => FileCategory::SPREADSHEET,
            $mimeType === 'application/pdf' => FileCategory::DOCUMENT,
            str_contains($mimeType, 'officedocument') => FileCategory::DOCUMENT,
            str_contains($mimeType, 'zip') || str_contains($mimeType, 'compressed') => FileCategory::ARCHIVE,
            default => FileCategory::OTHER,
        };
    }
}