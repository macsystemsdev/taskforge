<?php

namespace App\Filament\Reporting\Widgets\Project;

use App\Data\Reporting\Project\ProjectHealthData;
use App\Data\Reporting\Project\ProjectReportFilterData;
use App\Domain\Projects\Enums\ProjectHealthStatus;
use App\Models\Project;
use App\Services\Reporting\Cache\ProjectReportingCacheService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectHealthTableWidget extends TableWidget
{
    protected static ?string $heading = 'Project Health';
    protected static ?string $pollingInterval = '2m';
    protected int|string|array $columnSpan = 'full';
    protected static ?string $slug = 'reporting/projects-health';

    protected ProjectReportingCacheService $reporting;

    public function boot(ProjectReportingCacheService $reporting): void
    {
        $this->reporting = $reporting;
    }

    protected function filters(): ProjectReportFilterData
    {
        return new ProjectReportFilterData(
            organizationId: null,
            workspaceId: null,
            teamId: null,
        );
    }

    protected function getReportData(): array
    {
        return $this->reporting->health($this->filters())->map(fn(ProjectHealthData $dto) => [
            'project_id' => $dto->projectId,
            'project_name' => $dto->projectName,
            'project_slug' => $dto->projectSlug,
            'status' => $dto->status,
            'completion' => $dto->completionPercentage,
            'total_tasks' => $dto->totalTasks,
            'completed_tasks' => $dto->completedTasks,
            'in_progress_tasks' => $dto->inProgressTasks,
            'blocked_tasks' => $dto->blockedTasks,
            'overdue_tasks' => $dto->overdueTasks,
            'due_soon_tasks' => $dto->dueSoonTasks,
            'reason' => $dto->reason,
        ])->toArray();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Use a dummy query that returns no records - we'll override with our data
                Project::query()->whereRaw('1 = 0')
            )
            ->columns([
                Tables\Columns\TextColumn::make('project_name')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->getStateUsing(fn($record) => $record['project_name'] ?? null),

                Tables\Columns\TextColumn::make('status')
                    ->label('Health')
                    ->badge()
                    ->getStateUsing(fn($record) => $record['status'] ?? null)
                    ->color(fn($state): string => match ($state) {
                        ProjectHealthStatus::HEALTHY => 'success',
                        ProjectHealthStatus::AT_RISK => 'warning',
                        ProjectHealthStatus::CRITICAL => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn($state): string => match ($state) {
                        ProjectHealthStatus::HEALTHY => 'heroicon-o-check-circle',
                        ProjectHealthStatus::AT_RISK => 'heroicon-o-exclamation-triangle',
                        ProjectHealthStatus::CRITICAL => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn($state): string => $state?->label() ?? ucfirst($state?->value ?? 'Unknown')),

                Tables\Columns\TextColumn::make('completion')
                    ->label('Progress')
                    ->suffix('%')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['completion'] ?? 0)
                    ->color(fn($state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('total_tasks')
                    ->label('Total Tasks')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['total_tasks'] ?? 0),

                Tables\Columns\TextColumn::make('completed_tasks')
                    ->label('Completed')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['completed_tasks'] ?? 0)
                    ->color('success'),

                Tables\Columns\TextColumn::make('in_progress_tasks')
                    ->label('In Progress')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['in_progress_tasks'] ?? 0)
                    ->color('warning'),

                Tables\Columns\TextColumn::make('blocked_tasks')
                    ->label('Blocked')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['blocked_tasks'] ?? 0)
                    ->color('danger'),

                Tables\Columns\TextColumn::make('overdue_tasks')
                    ->label('Overdue')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['overdue_tasks'] ?? 0)
                    ->color('danger')
                    ->badge()
                    ->visible(fn($record) => ($record['overdue_tasks'] ?? 0) > 0),

                Tables\Columns\TextColumn::make('due_soon_tasks')
                    ->label('Due Soon')
                    ->numeric()
                    ->getStateUsing(fn($record) => $record['due_soon_tasks'] ?? 0)
                    ->color('warning')
                    ->badge()
                    ->visible(fn($record) => ($record['due_soon_tasks'] ?? 0) > 0),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(50)
                    ->getStateUsing(fn($record) => $record['reason'] ?? null)
                    ->tooltip(fn($record) => $record['reason'] ?? null)
                    ->visible(fn($record) => 
                        isset($record['reason']) && 
                        ($record['status'] ?? null) !== ProjectHealthStatus::HEALTHY
                    )
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Health Status')
                    ->options(
                        collect(ProjectHealthStatus::cases())
                            ->mapWithKeys(fn($case) => [
                                $case->value => $case->label() ?? ucfirst($case->value)
                            ])
                            ->toArray()
                    ),
            ])
            ->defaultSort('completion', 'asc')
            ->paginated([5, 10, 25, 50])
->defaultPaginationPageOption(10)
            ->striped()
            ->records(function () {
                // Get all data
                $allData = $this->getReportData();
                
                // Apply filters manually
                $status = request()->input('tableFilters.status.value');
                if ($status) {
                    $allData = array_filter(
                        $allData,
                        fn($item) => ($item['status']->value ?? null) === $status
                    );
                }

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
