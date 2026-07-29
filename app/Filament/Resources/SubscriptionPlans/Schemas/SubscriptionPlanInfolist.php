<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubscriptionPlanInfolist
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
                    ->description(
                        'Commercial terms cannot be edited. Create a new subscription plan version to change pricing, limits or billing.'
                    )
                    ->columns(2)
                    ->schema([

                        TextEntry::make('name'),

                        TextEntry::make('slug'),

                        TextEntry::make('price')
                            ->money(fn($record) => $record->currency),

                        TextEntry::make('currency'),

                        TextEntry::make('billing_interval')
                            ->badge(),

                        TextEntry::make('max_workspaces')
                            ->label('Maximum Workspaces')
                            ->formatStateUsing(
                                fn(?int $state) => $state ?? 'Unlimited'
                            ),

                        TextEntry::make('max_projects')
                            ->label('Maximum Projects')
                            ->formatStateUsing(
                                fn(?int $state) => $state ?? 'Unlimited'
                            ),

                        TextEntry::make('max_members')
                            ->label('Maximum Members')
                            ->formatStateUsing(
                                fn(?int $state) => $state ?? 'Unlimited'
                            ),

                        TextEntry::make('max_teams')
                            ->label('Maximum Teams')
                            ->formatStateUsing(
                                fn(?int $state) => $state ?? 'Unlimited'
                            ),

                        TextEntry::make('max_tasks')
                            ->label('Maximum Tasks')
                            ->formatStateUsing(
                                fn(?int $state) => $state ?? 'Unlimited'
                            ),

                        TextEntry::make('max_storage_mb')
                            ->label('Storage')
                            ->formatStateUsing(
                                fn(?int $state) => $state
                                    ? "{$state} MB"
                                    : 'Unlimited'
                            ),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Lifecycle
                |--------------------------------------------------------------------------
                |
                | Displays where this commercial contract currently sits in its
                | lifecycle. These values are controlled by lifecycle Actions rather
                | than direct CRUD editing.
                |
                */

                Section::make('Lifecycle')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('activated_at')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('retired_at')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('retirement_effective_at')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('archived_at')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime(),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Presentation
                |--------------------------------------------------------------------------
                |
                | TODO:
                | Replace this section with a shared pricing card preview once the
                | public pricing page is implemented. The preview should reuse the
                | same Blade component used by customers.
                |
                */

                Section::make('Presentation')
                    ->description(
                        'Presentation metadata is managed separately from the commercial contract.'
                    )
                    ->schema([

                        TextEntry::make('metadata.display_name')
                            ->label('Display Name'),

                        TextEntry::make('metadata.subtitle')
                            ->placeholder('-'),

                        TextEntry::make('metadata.badge')
                            ->badge()
                            ->placeholder('-'),

                    ]),

            ]);
    }
}
