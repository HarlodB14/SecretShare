<?php

use Illuminate\Support\Facades\Schedule;

// Safety net: prune any expired secrets every minute.
Schedule::command('secret:prune-expired')->everyMinute()->withoutOverlapping();

