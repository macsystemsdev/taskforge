<?php

namespace App\Models;

use App\Domain\Storage\Enums\FileCategory;
use App\Domain\Storage\Enums\FileVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('stored_files')]
#[Fillable([
    'organization_id',
    'workspace_id',
    'uploaded_by',
    'disk',
    'path',
    'stored_name',
    'original_name',
    'mime_type',
    'extension',
    'category',
    'visibility',
    'size',
    'checksum',
])]

class StoredFile extends Model
{
       protected function casts(): array
    {
        return [
            'category' => FileCategory::class,
            'visibility' => FileVisibility::class,
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by',
        );
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FileAttachment::class);
    }
}
