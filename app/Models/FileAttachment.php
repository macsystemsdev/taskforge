<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('file_attachments')]
#[Fillable([
    'stored_file_id',
    'comment_id',
    'created_by',
    'is_pinned',
    'attachable_type',
    'attachable_id',
])]
class FileAttachment extends Model
{
    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(
            StoredFile::class,
            'stored_file_id',
        );
    }

    public function storedFile(): BelongsTo
    {
        return $this->file();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    public function taskReferences(): HasMany
    {
        return $this->hasMany(
            TaskFileReference::class
        );
    }
}
