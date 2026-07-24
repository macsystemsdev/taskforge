<?php

namespace App\Services\Owner\Infrastructure;

use App\Services\Owner\DTO\MetricData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class InfrastructureMetricsService
{
    public function healthMetrics(): array
    {
        return [

            'queuedJobs' => new MetricData(
                label: 'Queued Jobs',
                value: $this->queuedJobs(),
                description: 'Pending jobs waiting for processing',
                icon: 'heroicon-o-queue-list',
                color: 'info',
            ),

            'failedJobs' => new MetricData(
                label: 'Failed Jobs',
                value: $this->failedJobs(),
                description: 'Jobs that failed execution',
                icon: 'heroicon-o-exclamation-triangle',
                color: 'danger',
            ),

            'redisStatus' => new MetricData(
                label: 'Redis',
                value: $this->redisStatus(),
                description: 'Redis infrastructure health',
                icon: 'heroicon-o-bolt',
                color: 'success',
            ),

            'databaseStatus' => new MetricData(
                label: 'Database',
                value: $this->databaseStatus(),
                description: 'Database connection health',
                icon: 'heroicon-o-circle-stack',
                color: 'success',
            ),

            'mailStatus' => new MetricData(
                label: 'Mail',
                value: $this->mailStatus(),
                description: 'Outgoing mail infrastructure',
                icon: 'heroicon-o-envelope',
                color: 'success',
            ),

        ];
    }

    // private function storageUsed(): int
    // {
    //     return 0;
    // }

    // private function storageLimit(): int
    // {
    //     return 0;
    // }

    // private function storageUsagePercentage(): int
    // {
    //     return 0;
    // }

        // 'storageUsed' => new MetricData(
            //     label: 'Storage Used',
            //     value: $this->storageUsed(),
            //     description: 'Total platform storage consumption',
            //     icon: 'heroicon-o-server-stack',
            //     color: 'warning',
            // ),

            // 'storageLimit' => new MetricData(
            //     label: 'Storage Limit',
            //     value: $this->storageLimit(),
            //     description: 'Maximum available storage',
            //     icon: 'heroicon-o-circle-stack',
            //     color: 'primary',
            // ),

            // 'storageUsagePercentage' => new MetricData(
            //     label: 'Storage Usage',
            //     value: $this->storageUsagePercentage(),
            //     description: 'Percentage of storage consumed',
            //     icon: 'heroicon-o-chart-pie',
            //     color: 'warning',
            // ),

    private function queuedJobs(): int
    {
        return Queue::size('default')
            + Queue::size('emails')
            + Queue::size('notifications')
            + Queue::size('activities');
    }

    private function failedJobs(): int
    {
        return DB::table('failed_jobs')->count();
    }

    private function redisStatus(): string
    {
        try {
            Redis::connection()->ping();

            return 'Operational';
        } catch (\Throwable) {
            return 'Offline';
        }
    }

    private function databaseStatus(): string
    {
        try {

            DB::connection()->getPdo();

            return 'Operational';
        } catch (\Throwable) {

            return 'Offline';
        }
    }

    private function mailStatus(): string
    {
        return config('mail.default')
            ? 'Configured'
            : 'Not Configured';
    }
}
