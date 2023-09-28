<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunSeeders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seeders:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all seeders';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Run all seeders
        $this->info('Running all seeders...');
        Artisan::call('db:seed');
        $this->info('All seeders completed.');
    }
}
