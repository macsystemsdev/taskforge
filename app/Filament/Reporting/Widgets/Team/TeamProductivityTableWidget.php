<?php

namespace App\Filament\Reporting\Widgets\Team;

use App\Data\Reporting\Team\TeamProductivityData;
use App\Data\Reporting\Team\TeamReportFilterData;
use App\Services\Reporting\Cache\TeamReportingCacheService;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Collection;

/**
 * Displays productivity information for teams.
 *
 * Reporting calculations are delegated entirely to the
 * reporting layer.
 */
class TeamProductivityTableWidget extends TableWidget
{
    protected static ?string $heading = 'Team Productivity';

    protected ?string $pollingInterval = '5m';

    protected int|string|array $columnSpan = 'full';

    protected function filters(): TeamReportFilterData
    {
        return new TeamReportFilterData(
            organizationId: null,
            workspaceId: null,
            teamId: null,
        );
    }

    protected function teams(): Collection
    {
        return app(TeamReportingCacheService::class)
            ->productivity(
                $this->filters(),
            );
    }

    public function records(): Collection
    {
        return $this->teams();
    }

    public function table(Table $table): Table
    {
        return $table

            ->records(fn () => $this->records())

            ->columns([

                TextColumn::make('teamName')
                    ->label('Team')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TeamProductivityData $record) => $record->status->color())
                    ->formatStateUsing(
                        fn (TeamProductivityData $record) => $record->status->label(),
                    ),

                TextColumn::make('productivityScore')
                    ->label('Score')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('completionPercentage')
                    ->label('Completion')
                    ->suffix('%'),

                TextColumn::make('members')
                    ->numeric(),

                TextColumn::make('projects')
                    ->numeric(),

                TextColumn::make('tasks')
                    ->numeric(),

                TextColumn::make('blockedTasks')
                    ->label('Blocked')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('overdueTasks')
                    ->label('Overdue')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('reason')
                    ->limit(40)
                    ->wrap(),

            ]);
    }
}