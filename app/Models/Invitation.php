<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('invitations')]
#[Fillable('organization_id', 'email', 'status', 'token', 'role', 'expires_at', 'rejection_reason', 'invited_by', 'accepted_at')]
class Invitation extends Model
{
    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->computed_status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getComputedStatusAttribute(): string
    {
        if (
            $this->status === 'pending'
            && $this->expires_at->isPast()
        ) {
            return 'expired';
        }

        return $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->computed_status) {
            'accepted' => 'green',
            'rejected' => 'red',
            'expired' => 'amber',
            'cancelled' => 'zinc',
            default => 'blue',
        };
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
