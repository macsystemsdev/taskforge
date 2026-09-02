<?php

namespace App\Domain\Storage\Actions;

use App\Domain\Storage\Enums\FileCategory;
use App\Domain\Storage\Enums\FileVisibility;
use App\Domain\Storage\Services\FileStorageService;
use App\Domain\Usage\Actions\IncreaseStorageUsageAction;
use App\Domain\Usage\Services\StorageQuotaService;
use App\Models\Organization;
use App\Models\StoredFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UploadStoredFileAction
{
    public function __construct(
        protected FileStorageService $storage,
        protected IncreaseStorageUsageAction $Increaseusage,
        protected StorageQuotaService $quota,
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

        $bytes = $file->getSize();

        // Atomic quota check with lock to prevent race conditions
        DB::transaction(function () use ($organization, $bytes) {
            $this->quota->ensureCanStore(
                $organization,
                $bytes,
            );
        });

        $checksum = hash_file('sha256', $file->getRealPath());

        // Check for duplicate file
        $existing = StoredFile::where('organization_id', $organization->id)
            ->where('checksum', $checksum)
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            $path = $this->storage
                ->store(
                    $file,
                    $directory,
                );


            $storedfile =    StoredFile::create([
                'organization_id' => $organization->id,
                'uploaded_by' => $uploadedBy,
                'disk' => 'private',
                'path' => $path,
                'stored_name' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $this->detectRealMimeType($file),
                'extension' => strtolower($file->getClientOriginalExtension()),
                'category' => $this->categoryFromMimeType($file->getMimeType()),
                'visibility' => FileVisibility::PROJECT,
                'size' => $file->getSize(),
                'checksum' => $checksum,
            ]);
            $this->Increaseusage->handle($organization, $bytes);

            return $storedfile;

        } 
        catch (\Throwable $exception) {
            if (isset($path)) {
                $this->storage->delete($path);
            }

            throw $exception;
        }
    }

    protected function detectRealMimeType(UploadedFile $file): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($file->getRealPath()) ?: 'application/octet-stream';
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
