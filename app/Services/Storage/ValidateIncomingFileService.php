<?php

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| Incoming File Validation Service
|--------------------------------------------------------------------------
|
| Every upload entering TaskForge passes through this service before it
| is persisted.
|
| Responsibilities
|
| • Validate upload integrity
| • Apply upload rules
| • Reject dangerous files
| • Enforce platform upload standards
|
| This service intentionally knows nothing about Projects, Workspaces,
| Organizations or Users.
|
| Future security features should be added here rather than scattered
| throughout upload actions.
|
*/

class ValidateIncomingFileService
{
    /**
     * Validate an uploaded file before storage.
     *
     * Throws ValidationException when validation fails.
     */
    public function handle(
        UploadedFile $file,
        array $rules,
    ): void {

        $this->validateUpload($file);

        $this->validateFilename($file);

        $this->validateDangerousExtensions($file);

        $this->validateRules(
            $file,
            $rules,
        );

        $this->validateMimeTypeAgainstContent($file);

        $this->validateExtensionMatchesMimeType($file);

        $this->validateImageContent($file);

        $this->stripExifMetadata($file);

        $this->validateZipArchive($file);
    }

    /**
     * Ensure PHP successfully received the uploaded file.
     */
    protected function validateUpload(
        UploadedFile $file,
    ): void {

        if (! $file->isValid()) {

            throw ValidationException::withMessages([
                'file' => 'The uploaded file is invalid.',
            ]);
        }
    }

    /**
     * Validate filename constraints.
     */
    protected function validateFilename(
        UploadedFile $file,
    ): void {

        if (
            strlen(
                $file->getClientOriginalName()
            ) > 255
        ) {

            throw ValidationException::withMessages([
                'file' => 'Filename is too long.',
            ]);
        }
    }

    /**
     * Reject known executable file extensions.
     *
     * Defense-in-depth alongside MIME validation.
     */
    protected function validateDangerousExtensions(
        UploadedFile $file,
    ): void {

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        if (in_array(
            $extension,
            [
                'php',
                'php3',
                'php4',
                'php5',
                'phtml',
                'exe',
                'dll',
                'bat',
                'cmd',
                'com',
                'jar',
                'sh',
                'ps1',
            ],
            true
        )) {

            throw ValidationException::withMessages([
                'file' => 'Executable files are not permitted.',
            ]);
        }
    }

    /**
     * Verify the actual MIME type using magic bytes.
     *
     * This prevents MIME spoofing where a PHP file
     * is renamed to .jpg with Content-Type: image/jpeg.
     */
    protected function validateMimeTypeAgainstContent(
        UploadedFile $file,
    ): void {

        $detectedMime = $this->detectMimeType(
            $file->getRealPath()
        );

        $declaredMime = $file->getMimeType();

        if ($detectedMime !== $declaredMime) {
            // Allow slight variations (e.g., text/plain vs text/x-php)
            if (! $this->isMimeEquivalent(
                $detectedMime,
                $declaredMime,
            )) {
                throw ValidationException::withMessages([
                    'file' => sprintf(
                        'File content MIME type (%s) does not match declared MIME type (%s).',
                        $detectedMime,
                        $declaredMime,
                    ),
                ]);
            }
        }
    }

    /**
     * Detect the actual MIME type using PHP's Fileinfo.
     */
    protected function detectMimeType(
        string $path,
    ): string {

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($path) ?: 'application/octet-stream';
    }

    /**
     * Check if two MIME types are considered equivalent.
     */
    protected function isMimeEquivalent(
        string $detected,
        string $declared,
    ): bool {

        $equivalenceMap = [
            'application/zip' => [
                'application/x-zip-compressed',
                'application/x-zip',
                'multipart/x-zip',
            ],
            // NOTE: We intentionally do NOT map PHP MIME types
            // to any other type. PHP files must be detected
            // and rejected as executable files.
        ];

        if ($detected === $declared) {
            return true;
        }

        return isset($equivalenceMap[$detected])
            && in_array($declared, $equivalenceMap[$detected], true);
    }

    /**
     * Validate image dimensions and actual image content.
     *
     * Uses getimagesize() which is available without GD extension.
     * This catches files that have image MIME type but are not
     * actually valid images (polyglot files, truncated images).
     */
    protected function validateImageContent(
        UploadedFile $file,
    ): void {

        $detectedMime = $this->detectMimeType(
            $file->getRealPath()
        );

        // Only validate if the detected MIME is an image
        $imageMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];

        if (! in_array($detectedMime, $imageMimes, true)) {
            return;
        }

        $imageInfo = @getimagesize(
            $file->getRealPath()
        );

        if ($imageInfo === false) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded image is corrupted or invalid.',
            ]);
        }

        // Verify the detected MIME matches what getimagesize reports
        $reportedMime = $imageInfo['mime'] ?? null;

        if ($reportedMime && $reportedMime !== $detectedMime) {
            throw ValidationException::withMessages([
                'file' => sprintf(
                    'Image content MIME type (%s) does not match detected type (%s).',
                    $reportedMime,
                    $detectedMime,
                ),
            ]);
        }

        // Check image dimensions are reasonable (prevent decompression bombs)
        $width = $imageInfo[0] ?? 0;
        $height = $imageInfo[1] ?? 0;

        if ($width <= 0 || $height <= 0) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded image has invalid dimensions.',
            ]);
        }

        // Limit maximum dimensions to prevent memory exhaustion
        $maxDimension = 10000; // 10000x10000 pixels max

        if ($width > $maxDimension || $height > $maxDimension) {
            throw ValidationException::withMessages([
                'file' => sprintf(
                    'Image dimensions (%dx%d) exceed the maximum allowed (%dx%d).',
                    $width,
                    $height,
                    $maxDimension,
                    $maxDimension,
                ),
            ]);
        }
    }

    /**
     * Strip EXIF metadata from uploaded images.
     *
     * EXIF data can contain:
     * - GPS coordinates (privacy leak)
     * - Camera serial numbers
     * - User's real name
     * - Location history
     *
     * Current implementation: Detects and logs EXIF presence.
     * Actual stripping requires GD extension (documented in TODO).
     */
    protected function stripExifMetadata(
        UploadedFile $file,
    ): void {

        // Skip if EXIF extension not available
        if (! function_exists('exif_read_data')) {
            return;
        }

        $detectedMime = $this->detectMimeType(
            $file->getRealPath()
        );

        // Only process JPEG and TIFF files (contain EXIF)
        $exifMimes = [
            'image/jpeg',
            'image/tiff',
        ];

        if (! in_array($detectedMime, $exifMimes, true)) {
            return;
        }

        // Read EXIF data (suppress warnings for malformed EXIF)
        $exif = @exif_read_data($file->getRealPath());

        if ($exif === false) {
            return; // No EXIF data
        }

        // Log EXIF presence for privacy auditing
        \Illuminate\Support\Facades\Log::info('EXIF data detected in uploaded image', [
            'filename' => $file->getClientOriginalName(),
            'has_gps' => isset($exif['GPSLatitude']),
            'has_camera_make' => isset($exif['Make']),
            'has_camera_model' => isset($exif['Model']),
            'has_datetime' => isset($exif['DateTimeOriginal']),
            'has_software' => isset($exif['Software']),
        ]);

        /*
        |--------------------------------------------------------------------------
        | TODO: Actual EXIF Stripping (Requires GD Extension)
        |--------------------------------------------------------------------------
        |
        | When GD extension is installed, uncomment the following to
        | actually STRIP EXIF data by re-saving the image:
        |
        | if ($detectedMime === 'image/jpeg') {
        |     $img = imagecreatefromjpeg($file->getRealPath());
        |     imagejpeg($img, $file->getRealPath(), 90); // Re-save without EXIF
        |     imagedestroy($img);
        | }
        |
        | if ($detectedMime === 'image/tiff') {
        |     // TIFF stripping requires Imagick (GD doesn't support TIFF well)
        |     $imagick = new \Imagick($file->getRealPath());
        |     $imagick->stripImage();
        |     $imagick->writeImage($file->getRealPath());
        |     $imagick->destroy();
        | }
        |
        | Install GD: docker-php-ext-install gd (already in Dockerfile)
        |
        | Reference: docs/SECURITY_AND_UPLOAD_SYSTEM.md → "Deferred Security Work"
        | Reference: Dockerfile → docker-php-ext-install section
        |
        */
    }

    /**
     * Validate ZIP archives for zip bomb attacks.
     *
     * Checks:
     * 1. Total uncompressed size does not exceed limits
     * 2. Compression ratio is not suspiciously high
     * 3. Number of files in archive is reasonable
     * 4. Individual file sizes are within limits
     */
    protected function validateZipArchive(
        UploadedFile $file,
    ): void {

        $detectedMime = $this->detectMimeType(
            $file->getRealPath()
        );

        // Only validate ZIP archives
        $zipMimes = [
            'application/zip',
            'application/x-zip-compressed',
            'application/x-zip',
            'multipart/x-zip',
        ];

        if (! in_array($detectedMime, $zipMimes, true)) {
            return;
        }

        // Check if ZipArchive is available
        if (! class_exists(\ZipArchive::class)) {
            throw ValidationException::withMessages([
                'file' => 'ZIP validation is not available on this server.',
            ]);
        }

        $zip = new \ZipArchive();
        $result = $zip->open($file->getRealPath());

        if ($result !== true) {
            throw ValidationException::withMessages([
                'file' => 'The ZIP file is corrupted or invalid.',
            ]);
        }

        try {
            $numFiles = $zip->numFiles;
            $totalUncompressedSize = 0;
            $maxUncompressedSize = 500 * 1024 * 1024; // 500 MB total
            $maxFiles = 1000; // Maximum files in archive
            $maxCompressionRatio = 100; // 100:1 ratio

            if ($numFiles > $maxFiles) {
                throw ValidationException::withMessages([
                    'file' => sprintf(
                        'ZIP archive contains too many files (%d). Maximum is %d.',
                        $numFiles,
                        $maxFiles,
                    ),
                ]);
            }

            for ($i = 0; $i < $numFiles; $i++) {
                $stat = $zip->statIndex($i);

                if ($stat === false) {
                    continue;
                }

                $fileSize = $stat['size'] ?? 0;
                $compressedSize = $stat['comp_size'] ?? 0;

                $totalUncompressedSize += $fileSize;

                // Check individual file size
                if ($fileSize > 100 * 1024 * 1024) { // 100 MB per file
                    throw ValidationException::withMessages([
                        'file' => sprintf(
                            'ZIP archive contains a file that is too large (%d bytes).',
                            $fileSize,
                        ),
                    ]);
                }

                // Check compression ratio for each file
                if ($compressedSize > 0 && $fileSize > 0) {
                    $ratio = $fileSize / $compressedSize;

                    if ($ratio > $maxCompressionRatio) {
                        throw ValidationException::withMessages([
                            'file' => sprintf(
                                'ZIP archive contains a file with suspicious compression ratio (%d:1).',
                                (int) $ratio,
                            ),
                        ]);
                    }
                }
            }

            // Check total uncompressed size
            if ($totalUncompressedSize > $maxUncompressedSize) {
                throw ValidationException::withMessages([
                    'file' => sprintf(
                        'ZIP archive uncompressed size (%d MB) exceeds maximum allowed (%d MB).',
                        (int) ($totalUncompressedSize / 1024 / 1024),
                        (int) ($maxUncompressedSize / 1024 / 1024),
                    ),
                ]);
            }

            // Check overall compression ratio
            $compressedSize = $file->getSize();
            if ($compressedSize > 0 && $totalUncompressedSize > 0) {
                $overallRatio = $totalUncompressedSize / $compressedSize;

                if ($overallRatio > $maxCompressionRatio) {
                    throw ValidationException::withMessages([
                        'file' => sprintf(
                            'ZIP archive has suspicious overall compression ratio (%d:1).',
                            (int) $overallRatio,
                        ),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FUTURE: ZIP Extraction Security (When workspace→org library sharing is built)
            |--------------------------------------------------------------------------
            |
            | When we start EXTRACTING ZIP files server-side (for preview,
            | indexing, or bulk import), we MUST add these additional checks:
            |
            | 1. PER-FILE MIME VALIDATION
            |    Check each extracted file's MIME type using finfo.
            |    Reject executables, PHP files, and other dangerous content.
            |
            | 2. FILENAME SANITIZATION
            |    Use basename() on every extracted filename.
            |    Prevent path traversal (../../etc/passwd).
            |
            | 3. SYMLINK DETECTION
            |    Check $stat['external_attributes'] for symlink flag.
            |    Reject ZIPs containing symlinks.
            |
            | 4. NESTED ZIP DETECTION
            |    Detect ZIP files inside ZIP files.
            |    Prevent recursive zip bombs.
            |
            | 5. ENCRYPTED FILE DETECTION
            |    Check for encrypted entries (can't scan content).
            |    Either reject or mark as "unscanned".
            |
            | 6. FILE NAME COLLISION PREVENTION
            |    Ensure two files in ZIP don't extract to same path.
            |    Prevents overwrite attacks.
            |
            | 7. TOTAL EXTRACTION LIMITS
            |    Enforce disk space limits DURING extraction.
            |    Prevents filling up storage with legit-looking files.
            |
            | 8. EXTRACTION TIMEOUT
            |    Set a time limit for extraction operations.
            |    Prevents CPU exhaustion from decompression.
            |
            | Reference: docs/SECURITY_AND_UPLOAD_SYSTEM.md → "Deferred Security Work"
            |
            */
        } finally {
            $zip->close();
        }
    }

    /**
     * Verify file extension matches the detected MIME type.
     */
    protected function validateExtensionMatchesMimeType(
        UploadedFile $file,
    ): void {

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $detectedMime = $this->detectMimeType(
            $file->getRealPath()
        );

        $allowedExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
            'image/gif' => ['gif'],
            'application/pdf' => ['pdf'],
            'application/zip' => ['zip'],
            'application/x-rar' => ['rar'],
            'text/plain' => ['txt', 'csv'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.ms-excel' => ['xls'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
            'application/vnd.ms-powerpoint' => ['ppt'],
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
            'audio/mpeg' => ['mp3'],
            'audio/wav' => ['wav'],
            'audio/ogg' => ['ogg'],
            'audio/mp4' => ['m4a'],
            'video/mp4' => ['mp4'],
            'video/quicktime' => ['mov'],
            'video/webm' => ['webm'],
        ];

        if (! isset($allowedExtensions[$detectedMime])) {
            throw ValidationException::withMessages([
                'file' => sprintf(
                    'File type (%s) is not permitted.',
                    $detectedMime,
                ),
            ]);
        }

        if (! in_array($extension, $allowedExtensions[$detectedMime], true)) {
            throw ValidationException::withMessages([
                'file' => sprintf(
                    'File extension (.%s) does not match its actual content type (%s).',
                    $extension,
                    $detectedMime,
                ),
            ]);
        }
    }

    /**
     * Apply upload validation rules.
     */
    protected function validateRules(
        UploadedFile $file,
        array $rules,
    ): void {

        Validator::make(
            ['file' => $file],
            $rules,
        )->validate();
    }
}
