<?php

namespace App\Filament\Resources\SubscriptionPlans\Pages;

use App\Actions\SubscriptionPlan\UpdateSubscriptionPlanMetadataAction;
use App\Data\SubscriptionPlan\UpdateSubscriptionPlanMetadataData;
use App\Filament\Resources\SubscriptionPlans\Schemas\SubscriptionPlanMetadataForm;
use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;

class ManageSubscriptionPlanMetadata extends EditRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $metadata = $this->record->metadata?->toArray() ?? [];

        return [
            ...$metadata,

            'plan_preview' => [
                'name' => $this->record->name,
                'price' => $this->record->price,
                'currency' => $this->record->currency,
                'billing_interval' => $this->record->billing_interval->value,
                'max_workspaces' => $this->record->max_workspaces,
                'max_projects' => $this->record->max_projects,
                'max_members' => $this->record->max_members,
                'max_teams' => $this->record->max_teams,
                'max_tasks' => $this->record->max_tasks,
                'max_storage_mb' => $this->record->max_storage_mb,
            ],
        ];
    }

    public function form(Schema $schema): Schema
    {
        return SubscriptionPlanMetadataForm::configure($schema);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Subscription plan metadata updated';
    }

    public function getHeading(): string
    {
        return 'Manage Subscription Plan Metadata';
    }

    public function getPlanForPreview()
    {
        return $this->record;
    }

    protected function afterFormUpdated(): void
    {
        $this->dispatch('formUpdated');
    }

    protected function handleRecordUpdate(
        Model $record,
        array $data,
    ): Model {
        app(UpdateSubscriptionPlanMetadataAction::class)
            ->handle(
                plan: $record,
                
                data: UpdateSubscriptionPlanMetadataData::from($data),
            );

        return $record->refresh();
    }
}
