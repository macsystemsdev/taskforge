<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MEMBER = 'member';
}
