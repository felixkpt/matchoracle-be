<?php

namespace App\Providers;

use App\Repositories\Address\AddressRepository;
use App\Repositories\Address\AddressRepositoryInterface;
use App\Repositories\Coach\CoachRepository;
use App\Repositories\Coach\CoachRepositoryInterface;
use App\Repositories\CoachContract\CoachContractRepository;
use App\Repositories\CoachContract\CoachContractRepositoryInterface;
use App\Repositories\Competition\CompetitionRepository;
use App\Repositories\Competition\CompetitionRepositoryInterface;
use App\Repositories\Continent\ContinentRepository;
use App\Repositories\Continent\ContinentRepositoryInterface;
use App\Repositories\Country\CountryRepository;
use App\Repositories\Country\CountryRepositoryInterface;
use App\Repositories\Game\GameRepository;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\GamePrediction\GamePredictionRepository;
use App\Repositories\GamePrediction\GamePredictionRepositoryInterface;
use App\Repositories\GameSource\GameSourceRepository;
use App\Repositories\GameSource\GameSourceRepositoryInterface;
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
use App\Repositories\Season\SeasonRepository;
use App\Repositories\Season\SeasonRepositoryInterface;
use App\Repositories\Status\StatusRepository;
use App\Repositories\Status\StatusRepositoryInterface;
use App\Repositories\Team\TeamRepository;
use App\Repositories\Team\TeamRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Venue\VenueRepository;
use App\Repositories\Venue\VenueRepositoryInterface;
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
        $this->app->singleton(GameSourceRepositoryInterface::class, GameSourceRepository::class);
        $this->app->singleton(StatusRepositoryInterface::class, StatusRepository::class);
        $this->app->singleton(PostStatusRepositoryInterface::class, PostStatusRepository::class);
        $this->app->singleton(ContinentRepositoryInterface::class, ContinentRepository::class);
        $this->app->singleton(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->singleton(CompetitionRepositoryInterface::class, CompetitionRepository::class);
        $this->app->singleton(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->singleton(AddressRepositoryInterface::class, AddressRepository::class);
        $this->app->singleton(CoachRepositoryInterface::class, CoachRepository::class);
        $this->app->singleton(VenueRepositoryInterface::class, VenueRepository::class);
        $this->app->singleton(CoachContractRepositoryInterface::class, CoachContractRepository::class);
        $this->app->singleton(SeasonRepositoryInterface::class, SeasonRepository::class);
        $this->app->singleton(GameRepositoryInterface::class, GameRepository::class);
        $this->app->singleton(GamePredictionRepositoryInterface::class, GamePredictionRepository::class);
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
