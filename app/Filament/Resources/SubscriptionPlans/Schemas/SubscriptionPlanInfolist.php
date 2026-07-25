<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SubscriptionPlanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('currency'),
                TextEntry::make('billing_interval')
                    ->badge(),
                TextEntry::make('max_workspaces')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_projects')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_members')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('max_teams')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_tasks')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('max_storage_mb')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('storage_used_bytes')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
