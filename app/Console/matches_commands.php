<?php
// Matches commands
$schedule->command('app:matches-handler --task=historical_results')->everyTwoHours(15);
$schedule->command('app:matches-handler --task=recent_results')->hourly();
$schedule->command('app:matches-handler --task=shallow_fixtures')->hourly();
$schedule->command('app:matches-handler --task=fixtures')->everyTwoHours(20);

// Match commands
$schedule->command('app:match-handler --task=historical_results')->everyTwoHours(25);
$schedule->command('app:match-handler --task=recent_results')->hourly();
$schedule->command('app:match-handler --task=shallow_fixtures')->hourly();
$schedule->command('app:match-handler --task=fixtures')->everyTwoHours(30);
