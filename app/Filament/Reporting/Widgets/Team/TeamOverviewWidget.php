<?php

namespace App\Filament\Reporting\Widgets\Team;

use App\Data\Reporting\Team\TeamReportFilterData;
use App\Services\Reporting\Cache\TeamReportingCacheService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Executive overview of team productivity.
 *
 * This widget contains no business logic.
 * It simply renders cached reporting metrics.
 */
class TeamOverviewWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '5m';

    protected function filters(): TeamReportFilterData
    {
        return new TeamReportFilterData(
            organizationId: null,
            workspaceId: null,
            teamId: null,
        );
    }

    protected function getStats(): array
    {
        $overview = app(TeamReportingCacheService::class)
            ->overview($this->filters());

        return [

            Stat::make(
                'Total Teams',
                $overview['total_teams'],
            )
                ->description('Tracked teams')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make(
                'High Productivity',
                $overview['high_productivity'],
            )
                ->description('Excellent performance')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make(
                'Normal Productivity',
                $overview['normal_productivity'],
            )
                ->description('Operating normally')
                ->icon('heroicon-o-minus-circle')
                ->color('info'),

            Stat::make(
                'Low Productivity',
                $overview['low_productivity'],
            )
                ->description('Needs attention')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make(
                'Idle Teams',
                $overview['idle_teams'],
            )
                ->description('No recent activity')
                ->icon('heroicon-o-pause-circle')
                ->color('danger'),

        ];
    }
}