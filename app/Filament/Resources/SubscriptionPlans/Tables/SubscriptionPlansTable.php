<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use App\Domain\Billing\BillingInterval;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | Identity
                |--------------------------------------------------------------------------
                */

                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn($record) => $record->slug),

                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                /*
                |--------------------------------------------------------------------------
                | Commercial
                |--------------------------------------------------------------------------
                */

                TextColumn::make('price')
                    ->money(
                        fn($record) => $record->currency
                    )
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('currency')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('billing_interval')
                    ->badge()
                    ->formatStateUsing(
                        fn($state) => $state->getLabel()
                    )
                    ->color(fn($state) => match ($state->value) {
                        'none' => 'gray',
                        'monthly' => 'info',
                        'yearly' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),

                /*
                |--------------------------------------------------------------------------
                | Limits
                |--------------------------------------------------------------------------
                */

                TextColumn::make('max_workspaces')
                    ->numeric()
                    ->placeholder('Unlimited')  // ✅ Shows when null
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('max_projects')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('max_members')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('max_teams')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('max_tasks')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('max_storage_mb')
                    ->numeric()
                    ->placeholder('Unlimited')
                     ->formatStateUsing(function ($state) {
                        if ($state === null) return null;  // Let placeholder handle it

                        if ($state < 1024) return round($state, 1) . ' MB';
                        if ($state < 1024 * 1024) return round($state / 1024, 1) . ' GB';
                        return round($state / (1024 * 1024), 1) . ' TB';
                    })
                    ->sortable()
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | Lifecycle
                |--------------------------------------------------------------------------
                */

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn($state) => $state
                            ? 'Active'
                            : 'Draft'
                    )
                    ->color(
                        fn($state) => $state
                            ? 'success'
                            : 'gray'
                    ),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                /*
                |--------------------------------------------------------------------------
                | Filters
                |--------------------------------------------------------------------------
                */

                SelectFilter::make('billing_interval')
                    ->options(
                        collect(BillingInterval::cases())
                            ->mapWithKeys(fn($interval) => [
                                $interval->value => $interval->getLabel()
                            ])
                            ->toArray()
                    ),

                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'retired' => 'Retired',
                        'archived' => 'Archived',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon('heroicon-o-eye'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession();
    }
}
