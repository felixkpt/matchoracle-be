<?php

/** @var \Illuminate\Console\Scheduling\Schedule $schedule */

// Odd commands
$schedule->command('app:odd-handler --task=historical_results')->hourly(14);
$schedule->command('app:odd-handler --task=recent_results')->hourly(24);
$schedule->command('app:odd-handler --task=shallow_fixtures')->everyThreeHours(34);
$schedule->command('app:odd-handler --task=fixtures')->everyFourHours(44);
