<?php

/** @var \Illuminate\Console\Scheduling\Schedule $schedule */

// Matches commands
$schedule->command('app:matches-handler --task=historical_results')->everyTwoHours(5);
$schedule->command('app:matches-handler --task=recent_results')->everyThreeHours(10);
$schedule->command('app:matches-handler --task=shallow_fixtures')->everyTwoHours(15);
$schedule->command('app:matches-handler --task=fixtures')->everyThreeHours(18);

// Match commands
$schedule->command('app:match-handler --task=historical_results')->hourly(20);
$schedule->command('app:match-handler --task=recent_results')->hourly(25);
$schedule->command('app:match-handler --task=shallow_fixtures')->everyThreeHours(30);
$schedule->command('app:match-handler --task=fixtures')->everyFourHours(45);
