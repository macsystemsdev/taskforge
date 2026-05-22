<?php

namespace App\Data\Invitations;

use Spatie\LaravelData\Data;

class InviteMemberData extends Data
{
    public function __construct(
        public int $organization_id,
        public string $email,
        public string $role,
        public int $invited_by,
    ) {}
}