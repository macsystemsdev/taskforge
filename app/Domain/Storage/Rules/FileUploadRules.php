<?php

namespace App\Domain\Storage\Rules;

use Illuminate\Validation\Rules\File;

class FileUploadRules
{
    /**
     * Standard uploads used throughout projects and workspaces.
     *
     * Supports documents, images, archives, audio and collaboration videos.
     */
    public static function standard(): array
    {
        return [

            'file' => [

                'required',

                File::types([

                    // Documents
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'pptx',
                    'txt',
                    'csv',

                    // Archives
                    'zip',
                    'rar',

                    // Images
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',

                    // Audio
                    'mp3',
                    'wav',
                    'ogg',
                    'm4a',

                    // Video
                    'mp4',
                    'mov',
                    'webm',

                ])->max(102400), // 100 MB

            ],

        ];
    }

    /**
     * Voice notes shared inside project discussions.
     */
    public static function voiceNote(): array
    {
        return [

            'file' => [

                'required',

                File::types([
                    'mp3',
                    'wav',
                    'ogg',
                    'm4a',
                    'webm',
                ])->max(25600), // 25 MB

            ],

        ];
    }

    /**
     * User and team avatars.
     */
    public static function avatar(): array
    {
        return [

            'file' => [

                'required',

                File::image()
                    ->max(2048), // 2 MB

            ],

        ];
    }

    /**
     * Organization logos and workspace icons.
     */
    public static function logo(): array
    {
        return [

            'file' => [

                'required',

                File::image()
                    ->max(2048), // 2 MB

            ],

        ];
    }

    public static function multipleStandard(): array
    {
        return [

            'files' => [
                'required',
                'array',
            ],

            'files.*' => [

                File::types([

                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'pptx',
                    'txt',
                    'csv',
                    'zip',
                    'rar',
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                    'mp3',
                    'wav',
                    'ogg',
                    'm4a',
                    'mp4',
                    'mov',
                    'webm',

                ])->max(102400),

            ],

        ];
    }
}
