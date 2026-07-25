<?php

namespace App\Filament\Resources\SubscriptionPlans\Pages;

use App\Actions\SubscriptionPlan\CreateSubscriptionPlanAction;
use App\Data\SubscriptionPlan\SubscriptionPlanData;
use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscriptionPlan extends CreateRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function handleRecordCreation(array $data): SubscriptionPlan
    {
        return app(CreateSubscriptionPlanAction::class)
            ->handle(
                SubscriptionPlanData::from($data)
            );
    }
}
