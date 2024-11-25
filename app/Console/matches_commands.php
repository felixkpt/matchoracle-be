<!-- app/Console/matches_commands.php -->
<?php
$schedule->command('app:match-handler --task=fixtures')->everySixHours(30);

/** @var \Illuminate\Console\Scheduling\Schedule $schedule */

// Matches commands
$schedule->command('app:matches-handler --task=historical_results')->everyTwoHours(15);
$schedule->command('app:matches-handler --task=recent_results')->everyThreeHours();
$schedule->command('app:matches-handler --task=shallow_fixtures')->everyTwoHours(18);
$schedule->command('app:matches-handler --task=fixtures')->everySixHours(20);

// Match commands
$schedule->command('app:match-handler --task=historical_results')->everyTwoHours(25);
$schedule->command('app:match-handler --task=recent_results')->everyThreeHours();
$schedule->command('app:match-handler --task=shallow_fixtures')->everyTwoHours(30);
$schedule->command('app:match-handler --task=fixtures')->everySixHours(45);
