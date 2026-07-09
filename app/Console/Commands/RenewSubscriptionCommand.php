<?php

namespace App\Console\Commands;

use App\Domain\Billing\Services\RenewSubscriptionsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:renew')]
#[Description('Renew all subscriptions that are due for renewal.')]
class RenewSubscriptionCommand extends Command
{
    public function __construct(
        protected RenewSubscriptionsService $service,
    ) {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->service->handle();

        $this->info(
            'Subscriptions renewed.'
        );
    }
}
