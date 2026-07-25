<?php

namespace App\Filament\Widgets;

use App\Models\Organization;
use App\Services\Owner\Organization\OrganizationHealthCacheService;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class OrganizationHealthTable extends TableWidget
{
    protected static ?string $heading = 'Organization Health';

    protected int|string|array $columnSpan = 'full';

    protected function health(): OrganizationHealthCacheService
    {
        return resolve(
            OrganizationHealthCacheService::class
        );
    }


    public function table(Table $table): Table
    {
        return $table

            /*
             |--------------------------------------------------------------------------
             | TODO (Phase 2)
             |--------------------------------------------------------------------------
             |
             | Filament tables are Eloquent-first. Once the dashboard grows,
             | replace this with a custom table datasource backed directly by
             | OrganizationHealthData objects so presentation never depends on
             | Eloquent queries.
             |
             */
            
         ->records(function (): Collection {
            return $this->health()->overview()->map(fn($item) => [
                'organizationName' => $item->organizationName ?? $item->name ?? null,
                'owner' => $item->owner ?? null,
                'plan' => $item->plan ?? null,
                'health' => $item->health ?? null,
                'members' => $item->members ?? 0,
                'workspaces' => $item->workspaces ?? 0,
                'teams' => $item->teams ?? 0,
                'projects' => $item->projects ?? 0,
                'tasks' => $item->tasks ?? 0,
                'storageUsed' => $item->storageUsed ?? 0,
                'storageLimit' => $item->storageLimit ?? 0,
                'storagePercentage' => $item->storagePercentage ?? 0,
                'lastActivity' => $item->lastActivity ?? null,
                'trialEndsAt' => $item->trialEndsAt ?? null,
                'subscriptionEndsAt' => $item->subscriptionEndsAt ?? null,
            ]);
        })
            ->columns(
                $this->columns()
            )
            ->filters(
                $this->filters()
            )
            ->actions(
                $this->actions()
            )
            ->bulkActions(
                $this->bulkActions()
            )
            ->defaultSort(
                'lastActivity',
                'desc'
            )
            ->paginated([10, 25, 50]);
    }

    protected function columns(): array
    {
        return [

            // Identity
            TextColumn::make('organizationName')
                ->label('Organization')
                ->searchable()
                ->sortable()
                ->weight(FontWeight::SemiBold),

            TextColumn::make('owner')
                ->label('Owner')
                ->searchable(),

            BadgeColumn::make('plan')
                ->label('Plan'),

            TextColumn::make('health')
                ->badge()
                ->label('Health'),

            // Collaboration
            TextColumn::make('members')
                ->numeric()
                ->sortable(),

            TextColumn::make('workspaces')
                ->numeric()
                ->sortable(),

            TextColumn::make('teams')
                ->numeric()
                ->sortable(),

            TextColumn::make('projects')
                ->numeric()
                ->sortable(),

            TextColumn::make('tasks')
                ->numeric()
                ->sortable(),

            // Infrastructure
            TextColumn::make('storageUsed')
                ->label('Storage Used')
                ->suffix(' MB')
                ->sortable(),

            TextColumn::make('storageLimit')
                ->label('Limit')
                ->suffix(' MB'),

            TextColumn::make('storagePercentage')
                ->label('Usage')
                ->suffix('%')
                ->badge()
                ->color(fn(float $state) => match (true) {
                    $state >= 90 => 'danger',
                    $state >= 75 => 'warning',
                    default => 'success',
                }),

            // Activity
            TextColumn::make('lastActivity')
                ->label('Last Activity')
                ->since()
                ->sortable(),

            TextColumn::make('trialEndsAt')
                ->label('Trial Ends')
                ->date(),

            TextColumn::make('subscriptionEndsAt')
                ->label('Subscription Ends')
                ->date(),
        ];
    }

    protected function filters(): array
    {
        return [

            // Future

        ];
    }

    protected function actions(): array
    {
        return [

            // Day 4

        ];
    }

    protected function bulkActions(): array
    {
        return [

            // Future

        ];
    }
}
