<?php

namespace App\Services\Owner\DTO;

use App\Models\Organization;
use App\Services\Owner\Enums\OrganizationHealth;
use Spatie\LaravelData\Data;
use Carbon\CarbonInterface;

readonly class OrganizationHealthData
{

   public function __construct(
    public int $organizationId,
    public int $subscriptionId,
    public string $organizationName,
    public string $owner,
    public string $plan,

    public int $members,
    public int $workspaces,
    public int $teams,
    public int $projects,
    public int $tasks,

    public float $storageUsed,
    public float $storageLimit,
    public float $storagePercentage,

    public ?CarbonInterface $lastActivity,
    public ?CarbonInterface $trialEndsAt,
    public ?CarbonInterface $subscriptionEndsAt,

    public OrganizationHealth $health,
) {}
}
