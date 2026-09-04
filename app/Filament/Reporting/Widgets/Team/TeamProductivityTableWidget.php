<?php

namespace App\Filament\Reporting\Widgets\Team;

use App\Data\Reporting\Team\TeamProductivityData;
use App\Data\Reporting\Team\TeamReportFilterData;
use App\Models\Team;
use App\Services\Reporting\Cache\TeamReportingCacheService;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Pagination\LengthAwarePaginator;

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

    protected function getReportData(): array
    {
        return $this->reporting->productivity($this->filters())->map(fn(TeamProductivityData $dto) => [
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
        ])->toArray();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Team::query()->whereRaw('1 = 0'))
            ->columns([
                TextColumn::make('team_name')  
                    ->label('Team')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->getStateUsing(fn($record) => $record['team_name'] ?? null),

                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(fn($record) => $record['status'] ?? null)
                    ->color(fn($state) => $state?->color() ?? 'gray')
                    ->formatStateUsing(fn($state) => $state?->label() ?? 'Unknown'),

                TextColumn::make('score') 
                    ->label('Score')
                    ->suffix('%')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record['score'] ?? 0),

                TextColumn::make('completion_percentage') 
                    ->label('Completion')
                    ->suffix('%')
                    ->getStateUsing(fn($record) => $record['completion_percentage'] ?? 0),

                TextColumn::make('member_count') 
                    ->label('Members')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['member_count'] ?? 0),

                TextColumn::make('project_count') 
                    ->label('Projects')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['project_count'] ?? 0),

                TextColumn::make('total_tasks') 
                    ->label('Tasks')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['total_tasks'] ?? 0),

                TextColumn::make('blocked_tasks') 
                    ->label('Blocked')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn($record) => $record['blocked_tasks'] ?? 0),

                TextColumn::make('overdue_tasks') 
                    ->label('Overdue')
                    ->badge()
                    ->color('danger')
                    ->getStateUsing(fn($record) => $record['overdue_tasks'] ?? 0),

                TextColumn::make('reason')
                    ->limit(40)
                    ->wrap()
                    ->getStateUsing(fn($record) => $record['reason'] ?? null),
            ])
            ->paginated([5, 10, 25, 50])
->defaultPaginationPageOption(10)
            ->striped()
            ->records(function () {
                // Get all data
                $allData = $this->getReportData();

                // Get pagination parameters
                $page = (int) $this->getTablePage();
                $perPage = (int) $this->getTableRecordsPerPage();
                $perPage = in_array($perPage, [5, 10, 25, 50]) ? $perPage : 10;
                $page = max(1, $page);

                // Paginate manually
                $total = count($allData);
                $offset = ($page - 1) * $perPage;
                $items = array_slice($allData, $offset, $perPage);

                return new LengthAwarePaginator(
                    collect($items),
                    $total,
                    $perPage,
                    $page,
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            });
    }
}
