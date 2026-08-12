<?php

namespace App\Services\Owner\Infrastructure;

use App\Models\OrganizationUsage;
use App\Services\Owner\DTO\MetricData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;


// Redis latency

// Queue processing time

// Mail latency

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

             'platformStorageUsed' => new MetricData(
                label: 'Platform Storage',
                value: $this->platformStorageUsed(),
                description: 'Application-managed storage consumption',
                icon: 'heroicon-o-server-stack',
                color: 'warning',
            ),

        ];
    }

    private function storageUsedBytes(): int
    {
        return (int) OrganizationUsage::query()
            ->sum('storage_used_bytes');
    }

    private function platformStorageUsed(): float
    {
        $bytes = OrganizationUsage::query()
            ->sum('storage_used_bytes');

        return $bytes / (1024 * 1024);
    }


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
