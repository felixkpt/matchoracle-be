<?php

namespace App\Http\Controllers\Dashboard\Countries;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Country\CountryRepositoryInterface;
use App\Services\Validations\Country\CountryValidationInterface;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private CountryRepositoryInterface $countryRepositoryInterface,
        private CountryValidationInterface $countryValidationInterface,
    ) {
        $this->repo = $countryRepositoryInterface;
    }

    function index()
    {
        return $this->countryRepositoryInterface->index();
    }

    function whereHasClubTeams()
    {
        return $this->countryRepositoryInterface->whereHasClubTeams();
    }

    function whereHasNationalTeams()
    {
        return $this->countryRepositoryInterface->whereHasNationalTeams();
    }

    public function store(Request $request)
    {

        $data = $this->countryValidationInterface->store($request);

        return $this->countryRepositoryInterface->store($request, $data);
    }
}
