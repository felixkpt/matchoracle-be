<?php

namespace App\Services\Filerepo;

use Illuminate\Support\ServiceProvider;

class FileRepoProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path('Services/Filerepo/functions.php');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('app/Services/Filerepo/file-repo.route.php'));
        $this->loadMigrationsFrom(base_path('app/Services/Filerepo/migrations'));
    }
}
