<?php

namespace App\Domain\Storage\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GetProjectAttachmentsService
{
    /**
     * Retrieve attachments belonging to a project.
     */
    public function handle(
        Project $project,
        ?string $search = null,
        string $sort = 'newest',
        ?string $type = null,
        ?int $perPage = null,
    ): Collection|LengthAwarePaginator {

        $query = $project
            ->fileAttachments()
            ->with([
                'storedFile',
                'uploader',
            ]);

        if ($search) {
            $query->whereHas('file', function (Builder $query) use ($search) {
                $query->where('original_name', 'like', '%' . $search . '%');
            });
        }

        if ($type && $type !== 'all') {
            $query->whereHas('file', function (Builder $query) use ($type) {
                if ($type === 'images') {
                    $query->where('mime_type', 'like', 'image/%');

                    return;
                }

                if ($type === 'documents') {
                    $query->where(function (Builder $query) {
                        $query->where('mime_type', 'application/pdf')
                            ->orWhere('mime_type', 'like', 'application/msword')
                            ->orWhere('mime_type', 'like', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                            ->orWhere('mime_type', 'like', 'text/%');
                    });

                    return;
                }

                if ($type === 'spreadsheets') {
                    $query->where(function (Builder $query) {
                        $query->where('mime_type', 'like', 'application/vnd.ms-excel')
                            ->orWhere('mime_type', 'like', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                            ->orWhere('mime_type', 'text/csv');
                    });

                    return;
                }

                $query->where(function (Builder $query) {
                    $query->whereNot('mime_type', 'like', 'image/%')
                        ->where('mime_type', '!=', 'application/pdf');
                });
            });
        }

        if ($sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        return $perPage
            ? $query->paginate($perPage)
            : $query->get();
    }
}