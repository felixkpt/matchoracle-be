<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Validations\Permission\PermissionValidation;
use App\Services\Validations\Permission\PermissionValidationInterface;
use App\Services\Validations\Post\PostValidation;
use App\Services\Validations\Post\PostValidationInterface;
use App\Services\Validations\Role\RoleValidation;
use App\Services\Validations\Role\RoleValidationInterface;
use App\Services\Validations\User\UserValidation;
use App\Services\Validations\User\UserValidationInterface;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RoleValidationInterface::class, RoleValidation::class);
        $this->app->bind(PermissionValidationInterface::class, PermissionValidation::class);
        $this->app->bind(PostValidationInterface::class, PostValidation::class);
        $this->app->bind(UserValidationInterface::class, UserValidation::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
