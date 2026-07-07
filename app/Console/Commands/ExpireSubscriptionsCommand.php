<?php

namespace App\Console\Commands;

use App\Domain\Billing\Services\ExpireSubscriptionsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:expire')]
#[Description('Expire subscriptions whose billing period has ended')]
class ExpireSubscriptionsCommand extends Command
{


    public function __construct(
        protected ExpireSubscriptionsService $service,
    ) {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->service->handle();

        $this->info('Subscriptions checked successfully.');

        return self::SUCCESS;
    }
}
