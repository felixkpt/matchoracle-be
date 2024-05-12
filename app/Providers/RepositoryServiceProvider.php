<?php

namespace App\Providers;

use App\Repositories\BettingStrategy\BettingStrategyRepository;
use App\Repositories\BettingStrategy\BettingStrategyRepositoryInterface;
use App\Repositories\BettingTips\BettingTipsRepository;
use App\Repositories\BettingTips\BettingTipsRepositoryInterface;
use App\Repositories\Game\GameRepository;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\GamePrediction\GamePredictionRepository;
use App\Repositories\GamePrediction\GamePredictionRepositoryInterface;
use App\Repositories\GameScoreStatus\GameScoreStatusRepository;
use App\Repositories\GameScoreStatus\GameScoreStatusRepositoryInterface;
use App\Repositories\Permission\PermissionRepository;
use App\Repositories\Permission\PermissionRepositoryInterface;
use App\Repositories\Post\Category\PostCategoryRepository;
use App\Repositories\Post\Category\PostCategoryRepositoryInterface;
use App\Repositories\Post\PostRepository;
use App\Repositories\Post\PostRepositoryInterface;
use App\Repositories\PostStatus\PostStatusRepository;
use App\Repositories\PostStatus\PostStatusRepositoryInterface;
use App\Repositories\Role\RoleRepository;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Repositories\Statistics\CompetitionPredictionStatisticsRepository;
use App\Repositories\Statistics\CompetitionPredictionStatisticsRepositoryInterface;
use App\Repositories\Statistics\CompetitionStatisticsRepository;
use App\Repositories\Statistics\CompetitionStatisticsRepositoryInterface;
use App\Repositories\Status\StatusRepository;
use App\Repositories\Status\StatusRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->singleton(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(PostRepositoryInterface::class, PostRepository::class);
        $this->app->singleton(PostCategoryRepositoryInterface::class, PostCategoryRepository::class);
        $this->app->singleton(StatusRepositoryInterface::class, StatusRepository::class);
        $this->app->singleton(PostStatusRepositoryInterface::class, PostStatusRepository::class);
        $this->app->singleton(GameScoreStatusRepositoryInterface::class, GameScoreStatusRepository::class);
        $this->app->singleton(GameRepositoryInterface::class, GameRepository::class);
        $this->app->singleton(GamePredictionRepositoryInterface::class, GamePredictionRepository::class);
        $this->app->singleton(CompetitionStatisticsRepositoryInterface::class, CompetitionStatisticsRepository::class);
        $this->app->singleton(CompetitionPredictionStatisticsRepositoryInterface::class, CompetitionPredictionStatisticsRepository::class);
        $this->app->singleton(BettingTipsRepositoryInterface::class, BettingTipsRepository::class);
        $this->app->singleton(BettingStrategyRepositoryInterface::class, BettingStrategyRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
