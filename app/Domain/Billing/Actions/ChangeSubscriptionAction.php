<?php

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\DataTranferObjects\ChangeSubscriptionData;
use App\Domain\Billing\Services\ChangeSubscriptionService;

class UpgradeSubscriptionAction
{
    public function __construct(
        protected ChangeSubscriptionService  $service,
    ) {}

    public function handle(
        ChangeSubscriptionData $data,
    ): string {
        return $this->service->handle($data);
    }
}
