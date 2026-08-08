<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:activate-pending')
    ->everyMinute();

Schedule::command('subscriptions:renew')
    ->everyMinute();

Schedule::command('subscriptions:expire')
    ->everyMinute();

Schedule::command('usage:recalculate')
    ->weekly();
