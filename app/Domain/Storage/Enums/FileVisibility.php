<?php

namespace App\Domain\Storage\Enums;

enum FileVisibility: string
{
    case ORGANIZATION = 'organization';
    case WORKSPACE = 'workspace';
    case PROJECT = 'project';

    public function label(): string
    {
        return match ($this) {
            self::ORGANIZATION => 'Organization',
            self::WORKSPACE => 'Workspace',
            self::PROJECT => 'Project',
        };
    }
}
