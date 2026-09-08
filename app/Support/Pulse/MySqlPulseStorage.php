<?php

namespace App\Support\Pulse;

use Laravel\Pulse\Storage\DatabaseStorage;

/**
 * MySQL 9.7 compatibility for Laravel Pulse.
 *
 * MySQL 9.7 disallows md5() inside generated columns, so Pulse
 * cannot rely on the database to populate key_hash automatically.
 * This override forces Pulse to populate key_hash in PHP for MySQL.
 */
class MySqlPulseStorage extends DatabaseStorage
{
    protected function requiresManualKeyHash(): bool
    {
        return true;
    }
}
