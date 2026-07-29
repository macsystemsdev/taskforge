<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use App\Domain\Billing\BillingInterval;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),

                TextInput::make('currency')
                    ->required()
                    ->default('USD')
                    ->maxLength(3)
                    ->afterStateHydrated(
                        fn($component, $state) =>
                        $component->state(strtoupper($state))
                    )
                    ->mutateDehydratedStateUsing(fn($state) => strtoupper($state)),

                Select::make('billing_interval')
                    ->required()
                    ->options(
                        collect(BillingInterval::cases())
                            ->mapWithKeys(
                                fn(BillingInterval $interval) => [
                                    $interval->value =>
                                    $interval->getLabel(),
                                ]
                            )
                            ->toArray()
                    ),

                TextInput::make('max_workspaces')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),

                TextInput::make('max_projects')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),

                TextInput::make('max_members')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),

                TextInput::make('max_teams')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),

                TextInput::make('max_tasks')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),

                TextInput::make('max_storage_mb')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
            ]);
    }
}
