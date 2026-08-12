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
                    ->placeholder('Unlimited')  // ✅ Shows when null
                    ->sortable(),

                TextColumn::make('max_projects')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable(),

                TextColumn::make('max_members')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable(),

                TextColumn::make('max_teams')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable(),

                TextColumn::make('max_tasks')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable(),

                TextColumn::make('max_storage_mb')
                    ->numeric()
                    ->placeholder('Unlimited')
                     ->formatStateUsing(function ($state) {
                        if ($state === null) return null;  // Let placeholder handle it

                        if ($state < 1024 * 1024 * 1024) return round($state / (1024 * 1024), 1) . ' MB';
                        return round($state / (1024 * 1024 * 1024), 1) . ' GB';
                    })
                    ->sortable(),

                TextColumn::make('storage_used_bytes')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;  // Let placeholder handle it

                        if ($state < 1024) return $state . ' B';
                        if ($state < 1024 * 1024) return round($state / 1024, 1) . ' KB';
                        if ($state < 1024 * 1024 * 1024) return round($state / (1024 * 1024), 1) . ' MB';
                        return round($state / (1024 * 1024 * 1024), 1) . ' GB';
                    })
                    ->sortable(),
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
