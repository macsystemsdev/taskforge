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

    protected ?string $pollingInterval = '2m';

    protected int|string|array $columnSpan = 'full';
    protected static ?string $slug = 'reporting/teams-productivity';

    protected TeamReportingCacheService $reporting;

    public function boot(TeamReportingCacheService $reporting): void
    {
        $this->reporting = $reporting;
    }

    protected function filters(): TeamReportFilterData
    {
        return new TeamReportFilterData(
            organizationId: null,
            workspaceId: null,
            teamId: null,
        );
    }

    protected function reportData(): Collection
    {
        return $this->reporting->productivity($this->filters());
    }

    public function table(Table $table): Table
    {
        return $table

            ->records(function (): Collection {
               return $this->reportData()->map(fn(TeamProductivityData $dto) => [

                    'team_id' => $dto->teamId,

                    'team_name' => $dto->teamName,

                    'status' => $dto->status,

                    'score' => $dto->score,

                    'member_count' => $dto->memberCount,

                    'project_count' => $dto->projectCount,

                    'total_tasks' => $dto->totalTasks,

                    'completed_tasks' => $dto->completedTasks,

                    'in_progress_tasks' => $dto->inProgressTasks,

                    'blocked_tasks' => $dto->blockedTasks,

                    'overdue_tasks' => $dto->overdueTasks,

                    'completion_percentage' => $dto->completionPercentage,

                    'reason' => $dto->reason,

                ]);
            })

             ->columns([
            TextColumn::make('team_name')  
                ->label('Team')
                ->searchable(),

            TextColumn::make('status')
                ->badge()
                ->color(fn($state) => $state->color())
                ->formatStateUsing(fn($state) => $state->label()),

            TextColumn::make('score') 
                ->label('Score')
                ->suffix('%')
                ->sortable(),

            TextColumn::make('completion_percentage') 
                ->label('Completion')
                ->suffix('%'),

            TextColumn::make('member_count') 
                ->label('Members')
                ->numeric(),

            TextColumn::make('project_count') 
                ->label('Projects')
                ->numeric(),

            TextColumn::make('total_tasks') 
                ->label('Tasks')
                ->numeric(),

            TextColumn::make('blocked_tasks') 
                ->label('Blocked')
                ->badge()
                ->color('warning'),

            TextColumn::make('overdue_tasks') 
                ->label('Overdue')
                ->badge()
                ->color('danger'),

            TextColumn::make('reason')
                ->limit(40)
                ->wrap(),
        ]);
    }
}
