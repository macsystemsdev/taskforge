<?php

namespace App\Filament\Resources\SubscriptionPlans\Pages;

use App\Actions\SubscriptionPlan\ActivateSubscriptionPlanAction;
use App\Actions\SubscriptionPlan\ArchiveSubscriptionPlanAction;
use App\Actions\SubscriptionPlan\RetireSubscriptionPlanAction;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Filament\Resources\SubscriptionPlans\SubscriptionPlanResource;
use App\Data\SubscriptionPlan\RetireSubscriptionPlanData;
use Filament\Actions\Action;
use App\Filament\Resources\SubscriptionPlans\Pages\ManageSubscriptionPlanMetadata;
use Carbon\Carbon;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;

class ViewSubscriptionPlan extends ViewRecord
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('metadata')
                ->label('Manage Metadata')
                ->url(
                    fn($record) => ManageSubscriptionPlanMetadata::getUrl([
                        'record' => $record,
                    ])
                ),

            // Activate
            Action::make('activate')
                ->label('Activate Plan')
                ->color('success')
                ->requiresConfirmation()
                ->visible(
                    fn() => $this->record->status === SubscriptionPlanStatus::DRAFT
                )
                ->action(function () {

                    app(ActivateSubscriptionPlanAction::class)
                        ->handle($this->record);

                    Notification::make()
                        ->title('Subscription plan activated')
                        ->success()
                        ->send();
                }),

            // Schedule Retirement
            Action::make('retire')
                ->label('Schedule Retirement')
                ->color('warning')
                ->form([
                    DatePicker::make('retirement_effective_at')
                        ->label('Retirement Effective Date')
                        ->required()
                        ->minDate(now()->addDay()),
                ])
                ->requiresConfirmation()
                ->hidden(
                    fn($record) =>
                    $record->status->isRetired()
                        || $record->status->isArchived()
                )
                ->action(function (array $data) {

                    app(RetireSubscriptionPlanAction::class)
                        ->handle(
                            plan: $this->record,
                            data: new RetireSubscriptionPlanData(
                                effectiveDate: Carbon::parse(
                                    $data['retirement_effective_at']
                                ),
                            ),
                        );

                    Notification::make()
                        ->title('Subscription plan retirement scheduled')
                        ->success()
                        ->send();
                }),

            // Archive
            Action::make('archive')
                ->label('Archive Plan')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(
                    fn() => in_array(
                        $this->record->status,
                        [
                            SubscriptionPlanStatus::RETIRED,
                        ]
                    )
                )
                ->action(function () {

                    app(ArchiveSubscriptionPlanAction::class)
                        ->handle($this->record);

                    Notification::make()
                        ->title('Subscription plan archived')
                        ->success()
                        ->send();
                }),
        ];
    }
}
