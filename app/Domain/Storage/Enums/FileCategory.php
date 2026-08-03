<?php

namespace App\Domain\Storage\Enums;

enum FileCategory: string
{
    case DOCUMENT = 'document';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case SPREADSHEET = 'spreadsheet';
    case ARCHIVE = 'archive';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DOCUMENT => 'Document',
            self::IMAGE => 'Image',
            self::VIDEO => 'Video',
            self::AUDIO => 'Audio',
            self::SPREADSHEET => 'Spreadsheet',
            self::ARCHIVE => 'Archive',
            self::OTHER => 'Other',
        };
    }
}
