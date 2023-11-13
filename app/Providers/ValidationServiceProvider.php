<?php

namespace App\Providers;

use App\Services\Validations\Team\Address\AddressValidation;
use App\Services\Validations\Team\Address\AddressValidationInterface;
use App\Services\Validations\Team\Coach\CoachValidation;
use App\Services\Validations\Team\Coach\CoachValidationInterface;
use App\Services\Validations\Competition\CompetitionValidation;
use App\Services\Validations\Competition\CompetitionValidationInterface;
use App\Services\Validations\Continent\ContinentValidation;
use App\Services\Validations\Continent\ContinentValidationInterface;
use App\Services\Validations\Country\CountryValidation;
use App\Services\Validations\Country\CountryValidationInterface;
use App\Services\Validations\Game\GameValidation;
use App\Services\Validations\Game\GameValidationInterface;
use App\Services\Validations\GameSource\GameSourceValidation;
use App\Services\Validations\GameSource\GameSourceValidationInterface;
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
use App\Services\Validations\Team\CoachContract\CoachContractValidation;
use App\Services\Validations\Team\CoachContract\CoachContractValidationInterface;
use App\Services\Validations\Team\TeamValidation;
use App\Services\Validations\Team\TeamValidationInterface;
use App\Services\Validations\Team\Venue\VenueValidation;
use App\Services\Validations\Team\Venue\VenueValidationInterface;
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
        $this->app->bind(GameSourceValidationInterface::class, GameSourceValidation::class);
        $this->app->bind(StatusValidationInterface::class, StatusValidation::class);
        $this->app->bind(PostStatusValidationInterface::class, PostStatusValidation::class);
        $this->app->bind(ContinentValidationInterface::class, ContinentValidation::class);
        $this->app->bind(CountryValidationInterface::class, CountryValidation::class);
        $this->app->bind(CompetitionValidationInterface::class, CompetitionValidation::class);
        $this->app->bind(TeamValidationInterface::class, TeamValidation::class);
        $this->app->bind(AddressValidationInterface::class, AddressValidation::class);
        $this->app->bind(CoachValidationInterface::class, CoachValidation::class);
        $this->app->bind(VenueValidationInterface::class, VenueValidation::class);
        $this->app->bind(CoachContractValidationInterface::class, CoachContractValidation::class);
        $this->app->bind(GameValidationInterface::class, GameValidation::class);
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
