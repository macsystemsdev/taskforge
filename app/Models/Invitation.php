<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
#[Table('invitations')]
#[Fillable('organization_id', 'email', 'satus', 'token', 'role', 'expires_at', 'invited_by', 'accepted_at')]
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
}
