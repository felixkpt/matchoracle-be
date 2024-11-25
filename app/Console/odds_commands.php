<!-- app/Console/odds_commands.php -->
<?php
$schedule->command('app:match-handler --task=fixtures')->everySixHours(30);

/** @var \Illuminate\Console\Scheduling\Schedule $schedule */

// Odd commands
$schedule->command('app:odd-handler --task=historical_results')->everyThreeHours(29);
$schedule->command('app:odd-handler --task=recent_results')->everySixHours();
$schedule->command('app:odd-handler --task=shallow_fixtures')->everyThreeHours(34);
$schedule->command('app:odd-handler --task=fixtures')->twiceDaily();
