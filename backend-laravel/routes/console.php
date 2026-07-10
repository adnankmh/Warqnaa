<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('warqna:purge-inactive-accounts --days=30')
    ->dailyAt('03:30')
    ->withoutOverlapping();
