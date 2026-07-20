<?php

namespace App\Services\Owner\Infrastructure;

use App\Services\Owner\DTO\MetricData;

class InfrastructureMetricsService
{
    public function metrics(): array
    {
        return [

            'storageUsed' => new MetricData(label: StorageUsed, value: this->storageUsed()),

            'storageLimit' => [
                'value' => $this->storageLimit(),
            ],

            'storageUsagePercentage' => [
                'value' => $this->storageUsagePercentage(),
            ],

            'queuedJobs' => [
                'value' => $this->queuedJobs(),
            ],

            'failedJobs' => [
                'value' => $this->failedJobs(),
            ],

            'redisStatus' => [
                'value' => $this->redisStatus(),
            ],

            'databaseStatus' => [
                'value' => $this->databaseStatus(),
            ],

            'mailStatus' => [
                'value' => $this->mailStatus(),
            ],

        ];
    }
}
