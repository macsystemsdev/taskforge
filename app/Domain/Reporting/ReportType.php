<?php

namespace App\Domain\Reporting;

enum ReportType: string
{
    case TASK = 'task';

    case PROJECT = 'project';

    case TEAM = 'team';

    case ORGANIZATION = 'organization';

    case BILLING = 'billing';

    case INFRASTRUCTURE = 'infrastructure';

    public function label(): string
    {
        return match ($this) {
            self::TASK => 'Task Report',
            self::PROJECT => 'Project Report',
            self::TEAM => 'Team Report',
            self::ORGANIZATION => 'Organization Report',
            self::BILLING => 'Billing Report',
            self::INFRASTRUCTURE => 'Infrastructure Report',
        };
    }
}
