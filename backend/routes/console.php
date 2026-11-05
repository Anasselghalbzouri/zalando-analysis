<?php

use Illuminate\Support\Facades\Schedule;

// Heartbeat for the scheduler container's health check (docker-architecture §6):
// the check fails when this file is older than 3 minutes.
Schedule::call(fn () => touch(storage_path('framework/scheduler-heartbeat')))
    ->name('scheduler-heartbeat')
    ->everyMinute();
