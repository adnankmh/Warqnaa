<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('warqna:purge-cancelled-accounts')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('warqna:cleanup-voice')
    ->everyMinute()
    ->withoutOverlapping();
