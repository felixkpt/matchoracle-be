<?php

namespace App\Providers;

use App\Services\Validations\BettingStrategy\BettingStrategyValidation;
use App\Services\Validations\BettingStrategy\BettingStrategyValidationInterface;
use App\Services\Validations\Game\GameValidation;
use App\Services\Validations\Game\GameValidationInterface;
use App\Services\Validations\GameScoreStatus\GameScoreStatusValidation;
use App\Services\Validations\GameScoreStatus\GameScoreStatusValidationInterface;
use App\Services\Validations\Permission\PermissionValidation;
use App\Services\Validations\Permission\PermissionValidationInterface;
use App\Services\Validations\Post\Category\PostCategoryValidation;
use App\Services\Validations\Post\Category\PostCategoryValidationInterface;
use App\Services\Validations\Post\PostValidation;
use App\Services\Validations\Post\PostValidationInterface;
use App\Services\Validations\PostStatus\PostStatusValidation;
use App\Services\Validations\PostStatus\PostStatusValidationInterface;
use App\Services\Validations\Role\RoleValidation;
use App\Services\Validations\Role\RoleValidationInterface;
use App\Services\Validations\Status\StatusValidation;
use App\Services\Validations\Status\StatusValidationInterface;
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
        $this->app->bind(PostCategoryValidationInterface::class, PostCategoryValidation::class);
        $this->app->bind(StatusValidationInterface::class, StatusValidation::class);
        $this->app->bind(PostStatusValidationInterface::class, PostStatusValidation::class);
        $this->app->bind(GameScoreStatusValidationInterface::class, GameScoreStatusValidation::class);
        $this->app->bind(GameValidationInterface::class, GameValidation::class);
        $this->app->bind(BettingStrategyValidationInterface::class, BettingStrategyValidation::class);
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
