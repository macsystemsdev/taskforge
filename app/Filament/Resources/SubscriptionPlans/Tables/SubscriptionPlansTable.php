<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('price')
                    ->money(
                        fn($record) => $record->currency
                    )
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('billing_interval')
                    ->badge()
                    ->formatStateUsing(
                        fn($state) => $state->getLabel()
                    )
                    ->searchable(),
                TextColumn::make('max_workspaces')
                    ->numeric()
                    ->formatStateUsing(
                        fn($state) => $state ?? 'Unlimited'
                    )
                    ->sortable(),
                TextColumn::make('max_projects')
                    ->numeric()
                    ->formatStateUsing(
                        fn($state) => $state ?? 'Unlimited'
                    )
                    ->sortable(),
                TextColumn::make('max_members')
                    ->numeric()
                    ->formatStateUsing(
                        fn($state) => $state ?? 'Unlimited'
                    )
                    ->sortable(),
                TextColumn::make('max_teams')
                    ->numeric()
                    ->formatStateUsing(
                        fn($state) => $state ?? 'Unlimited'
                    )
                    ->sortable(),
                TextColumn::make('max_tasks')
                    ->numeric()
                    ->formatStateUsing(
                        fn($state) => $state ?? 'Unlimited'
                    )
                    ->sortable(),
                TextColumn::make('max_storage_mb')
                    ->numeric()
                    ->formatStateUsing(
                        fn($state) => $state ?? 'Unlimited'
                    )
                    ->sortable(),
                TextColumn::make('storage_used_bytes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->badge()
                    ->formatStateUsing(
                        fn(bool $state) => $state
                            ? 'Active'
                            : 'Inactive'
                    )
                    ->color(
                        fn(bool $state) => $state
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
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
