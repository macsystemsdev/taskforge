<?php

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\DataTransferObjects\CheckoutData;
use App\Domain\Billing\DataTransferObjects\CheckoutResponse;
use App\Domain\Billing\Services\CreateCheckoutService;

class CreateCheckoutAction
{
    public function __construct(
        protected CreateCheckoutService $service,
    ) {}

    public function handle(
        CheckoutData $data,
    ): CheckoutResponse {
        return $this->service->handle($data);
    }
}
