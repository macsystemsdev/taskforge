<?php

namespace App\Domain\Storage\Services;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileStorageService
{
    /**
     * Store an uploaded file using a generated UUID filename.
     */
    public function store(
        UploadedFile $file,
        string $directory,
    ): string {

        $filename = sprintf(
            '%s.%s',
            Str::uuid(),
            $file->extension(),
        );

        return $file->storeAs(
            $directory,
            $filename,
            'private',
        );
    }

    /**
     * Delete a file.
     *
     * Returns true if the delete operation succeeded.
     */
    public function delete(
        string $path,
    ): bool {

        return Storage::disk('private')
            ->delete($path);
    }

    /**
     * Determine whether a file exists.
     */
    public function exists(
        string $path,
    ): bool {

        return Storage::disk('private')
            ->exists($path);
    }

    /**
     * Download a stored file.
     */
    public function download(
        string $path,
        string $filename,
    ): StreamedResponse {

        return Storage::disk('private')
            ->download(
                $path,
                $filename,
            );
    }

    /**
     * Generate a public URL.
     */
    public function url(
        string $path,
    ): string {

        return Storage::disk('private')
            ->url($path);
    }

    /**
     * Generate a temporary URL.
     */
    public function temporaryUrl(
        string $path,
        CarbonInterface $expiresAt,
    ): string {

        return Storage::disk('private')
            ->temporaryUrl(
                $path,
                $expiresAt,
            );
    }

    /**
     * Move a file.
     */
    public function move(
        string $from,
        string $to,
    ): bool {

        return Storage::disk('private')
            ->move($from, $to);
    }

    /**
     * Copy a file.
     */
    public function copy(
        string $from,
        string $to,
    ): bool {

        return Storage::disk('private')
            ->copy($from, $to);
    }

    /**
     * Retrieve the file size in bytes.
     */
    public function size(
        string $path,
    ): int {

        return Storage::disk('private')
            ->size($path);
    }

    /**
     * Retrieve the MIME type.
     */
    public function mimeType(
        string $path,
    ): string {

        return Storage::disk('private')
            ->mimeType($path);
    }

    /**
     * Preview ZIP archive contents without extracting.
     *
     * Returns file names and metadata only.
     * Never extracts file content.
     */
    public function previewZip(
        string $path,
        int $maxFiles = 100,
    ): array {

        $zip = new \ZipArchive();
        $result = $zip->open(
            Storage::disk('private')->path($path)
        );

        if ($result !== true) {
            return [];
        }

        $files = [];
        $totalFiles = $zip->numFiles;

        for ($i = 0; $i < min($totalFiles, $maxFiles); $i++) {
            $stat = $zip->statIndex($i);

            if ($stat === false) {
                continue;
            }

            $files[] = [
                'name' => basename($stat['name']),
                'size' => $stat['size'] ?? 0,
                'compressed_size' => $stat['comp_size'] ?? 0,
                'is_directory' => substr($stat['name'], -1) === '/',
            ];
        }

        $zip->close();

        return [
            'files' => $files,
            'total_files' => $totalFiles,
            'truncated' => $totalFiles > $maxFiles,
        ];
    }
}