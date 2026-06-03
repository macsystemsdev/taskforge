<?php

namespace App\Models;

use App\Enums\OrganizationRole;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table('organization_user')]
class OrganizationUser extends Pivot
{

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'invited_by',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function isOwner(): bool
    {
        return $this->role === OrganizationRole::OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === OrganizationRole::ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === OrganizationRole::MEMBER;
    }

    protected function casts(): array
    {
        return [
            'role' => OrganizationRole::class,
        ];
    }



    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // Activity logs for this organization user morphMany
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
