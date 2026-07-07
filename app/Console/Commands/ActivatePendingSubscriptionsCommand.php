<?php

namespace App\Console\Commands;

use App\Domain\Billing\Services\ActivatePendingSubscriptionsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:activate-pending')]
#[Description('Activate pending subscription plans for organizations')]
class ActivatePendingSubscriptionsCommand extends Command
{

    public function __construct(protected ActivatePendingSubscriptionsService $service)
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $this->service->handle();

        $this->info(
            'Pending subscriptions activated.'
        );

        return self::SUCCESS;
    }
}
