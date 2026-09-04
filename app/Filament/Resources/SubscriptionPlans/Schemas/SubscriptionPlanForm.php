<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use App\Domain\Billing\BillingInterval;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Commercial Contract
                |--------------------------------------------------------------------------
                |
                | These fields define the commercial agreement customers subscribe to.
                | They are immutable after activation. Any commercial change requires
                | creating a new Subscription Plan version.
                |
                */

                Section::make('Commercial Contract')
                    ->description('Define pricing and limits. These cannot be changed after activation.')
                    ->icon('heroicon-o-banknotes')
                    ->columns(2)
                    ->schema([

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Pro Plan')
                            ->helperText('Display name for this subscription plan'),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->placeholder('29.00')
                            ->helperText('Monthly or yearly price in USD'),

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
                            )
                            ->native(false)
                            ->helperText('How often customers are billed'),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Resource Limits
                |--------------------------------------------------------------------------
                |
                | These define what the plan includes. Null values mean unlimited.
                |
                */

                Section::make('Resource Limits')
                    ->description('Set to null for unlimited access')
                    ->icon('heroicon-o-cube')
                    ->columns(3)
                    ->schema([

                        TextInput::make('max_workspaces')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder('Unlimited')
                            ->helperText('Maximum workspaces per organization'),

                        TextInput::make('max_projects')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder('Unlimited')
                            ->helperText('Maximum projects per workspace'),

                        TextInput::make('max_members')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder('Unlimited')
                            ->helperText('Maximum members per workspace'),

                        TextInput::make('max_teams')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder('Unlimited')
                            ->helperText('Maximum teams per workspace'),

                        TextInput::make('max_tasks')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder('Unlimited')
                            ->helperText('Maximum tasks per project'),

                        TextInput::make('max_storage_mb')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder('Unlimited')
                            ->suffix('MB')
                            ->helperText('Storage limit in megabytes'),
                    ]),
            ]);
    }
}
