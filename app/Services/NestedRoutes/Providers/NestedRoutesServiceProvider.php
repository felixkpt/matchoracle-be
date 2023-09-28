<?php

namespace App\Services\NestedRoutes\Providers;

use App\Services\NestedRoutes\Http\Middleware\NestedRoutesAuthMiddleware;
use App\Services\NestedRoutes\Http\Middleware\TemporaryTokenValidationMiddleware;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class NestedRoutesServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path('Services/NestedRoutes/functions.php');
    }
    
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('nested_routes_auth', NestedRoutesAuthMiddleware::class);
        $router->aliasMiddleware('temporary_token', TemporaryTokenValidationMiddleware::class);
        $this->loadRoutesFrom(base_path('routes/'.config('nested_routes.admin_folder').'/driver.php'));
    }
}
