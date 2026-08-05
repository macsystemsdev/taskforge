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
            'public',
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

        return Storage::disk('public')
            ->delete($path);
    }

    /**
     * Determine whether a file exists.
     */
    public function exists(
        string $path,
    ): bool {

        return Storage::disk('public')
            ->exists($path);
    }

    /**
     * Download a stored file.
     */
    public function download(
        string $path,
        string $filename,
    ): StreamedResponse {

        return Storage::disk('public')
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

        return Storage::disk('public')
            ->url($path);
    }

    /**
     * Generate a temporary URL.
     */
    public function temporaryUrl(
        string $path,
        CarbonInterface $expiresAt,
    ): string {

        return Storage::disk('public')
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

        return Storage::disk('public')
            ->move($from, $to);
    }

    /**
     * Copy a file.
     */
    public function copy(
        string $from,
        string $to,
    ): bool {

        return Storage::disk('public')
            ->copy($from, $to);
    }

    /**
     * Retrieve the file size in bytes.
     */
    public function size(
        string $path,
    ): int {

        return Storage::disk('public')
            ->size($path);
    }

    /**
     * Retrieve the MIME type.
     */
    public function mimeType(
        string $path,
    ): string {

        return Storage::disk('public')
            ->mimeType($path);
    }
}